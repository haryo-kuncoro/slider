# -*- coding: utf-8 -*-
"""
AUTO CROP PASFOTO - V4 FINAL
============================

Tujuan:
- Output selalu 300 x 400 px (rasio 3:4)
- Ukuran wajah dibuat konsisten
- Tepi ATAS RAMBUT / JILBAB menjadi patokan crop atas
- Tidak bergantung pada bounding box wajah untuk batas atas
- Preview:
    BIRU   = bounding box wajah
    MERAH  = tepi atas rambut/jilbab yang terdeteksi
    HIJAU  = crop final
    KUNING = garis tengah crop

PENTING:
Versi ini TIDAK membutuhkan MediaPipe.
Deteksi kepala dilakukan dengan OpenCV GrabCut pada area kepala,
kemudian hasilnya divalidasi dengan bentuk/posisi rambut atau jilbab.

Install:
    pip install opencv-python numpy

Jalankan:
    python auto_crop_pasfoto_v4_final.py
"""

import os
import shutil
from pathlib import Path
import tkinter as tk
from tkinter import filedialog, messagebox

import cv2
import numpy as np


# ============================================================
# PENGATURAN UTAMA
# ============================================================

OUTPUT_W = 300
OUTPUT_H = 400

# Target lebar wajah pada output 300 px.
# Ini membuat wajah antar foto lebih konsisten.
TARGET_FACE_W = 165

# Batas pengaman ukuran wajah.
MIN_OUTPUT_FACE_W = 150
MAX_OUTPUT_FACE_W = 180

# Posisi pusat wajah pada hasil.
# 0.42 = sedikit di atas tengah.
FACE_CENTER_Y = 0.42

# Jarak minimal antara tepi atas rambut/jilbab dengan frame.
# 0.06 = 6% tinggi crop = 24 px pada output 400 px.
TOP_MARGIN_RATIO = 0.06

# GrabCut:
# Area kepala dibuat sekitar lebar wajah x faktor berikut.
HEAD_ROI_WIDTH = 2.8

# Area kepala di atas wajah.
HEAD_ROI_ABOVE = 1.45

# Area kepala di bawah wajah.
HEAD_ROI_BELOW = 0.35

# Threshold mask hasil GrabCut.
# 0 = background, 1 = foreground.
# Probable foreground juga diterima.
FG_THRESHOLD = 0.5

# Validasi agar noise tidak dianggap rambut.
MIN_HEAD_WIDTH_RATIO = 0.10
MIN_SUPPORT_ROWS = 4

IMAGE_EXTENSIONS = {
    ".jpg", ".jpeg", ".png", ".bmp", ".webp", ".tif", ".tiff"
}


# ============================================================
# FOLDER
# ============================================================

def pilih_folder():
    root = tk.Tk()
    root.withdraw()
    root.attributes("-topmost", True)

    folder = filedialog.askdirectory(
        title="Pilih folder yang berisi foto"
    )

    root.destroy()
    return folder


# ============================================================
# FILE IMAGE
# ============================================================

def baca_gambar(path):
    data = np.fromfile(str(path), dtype=np.uint8)
    return cv2.imdecode(data, cv2.IMREAD_COLOR)


def simpan_gambar(path, image, quality=95):
    ext = Path(path).suffix.lower()

    if ext in (".jpg", ".jpeg"):
        ok, encoded = cv2.imencode(
            ".jpg",
            image,
            [cv2.IMWRITE_JPEG_QUALITY, quality]
        )
    else:
        ok, encoded = cv2.imencode(ext, image)

    if not ok:
        return False

    encoded.tofile(str(path))
    return True


# ============================================================
# DETEKSI WAJAH
# ============================================================

def load_face_cascade():
    path = cv2.data.haarcascades + "haarcascade_frontalface_default.xml"

    if not os.path.exists(path):
        raise RuntimeError(
            "File Haar Cascade tidak ditemukan:\n" + path
        )

    cascade = cv2.CascadeClassifier(path)

    if cascade.empty():
        raise RuntimeError("Haar Cascade gagal dimuat.")

    return cascade


def detect_faces(image, cascade):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    gray = cv2.equalizeHist(gray)

    h, w = gray.shape[:2]

    # Foto pasfoto biasanya wajah cukup besar.
    min_face = max(40, int(min(w, h) * 0.08))

    faces = cascade.detectMultiScale(
        gray,
        scaleFactor=1.06,
        minNeighbors=6,
        minSize=(min_face, min_face)
    )

    if len(faces) == 0:
        faces = cascade.detectMultiScale(
            gray,
            scaleFactor=1.04,
            minNeighbors=4,
            minSize=(30, 30)
        )

    return faces


def pilih_wajah(faces, image):
    """
    Pilih wajah terbaik.

    Prioritas:
    1. wajah besar
    2. dekat dengan tengah horizontal
    3. tidak terlalu dekat bagian bawah
    """

    if len(faces) == 0:
        return None

    H, W = image.shape[:2]
    cx_img = W / 2

    best = None
    best_score = -1e9

    for x, y, w, h in faces:
        cx = x + w / 2
        cy = y + h / 2

        area_ratio = (w * h) / (W * H)

        horizontal_error = abs(cx - cx_img) / W

        # Wajah yang terlalu dekat bagian bawah diberi penalti.
        vertical_error = max(0, (cy / H) - 0.62)

        score = (
            area_ratio * 8
            - horizontal_error * 1.5
            - vertical_error * 2
        )

        if score > best_score:
            best_score = score
            best = (int(x), int(y), int(w), int(h))

    return best


# ============================================================
# DETEKSI RAMBUT / JILBAB DENGAN GRABCUT
# ============================================================

def clean_mask(mask):
    """
    Bersihkan mask foreground dari noise.
    """

    mask = (mask > 0).astype(np.uint8)

    kernel_small = np.ones((3, 3), np.uint8)
    kernel_medium = np.ones((5, 5), np.uint8)

    mask = cv2.morphologyEx(
        mask,
        cv2.MORPH_OPEN,
        kernel_small,
        iterations=1
    )

    mask = cv2.morphologyEx(
        mask,
        cv2.MORPH_CLOSE,
        kernel_medium,
        iterations=2
    )

    return mask


def largest_component_near_face(mask, face, roi_offset):
    """
    Ambil komponen foreground yang paling dekat dengan wajah.
    Ini penting agar background yang ikut GrabCut tidak dipakai.
    """

    if mask is None:
        return None

    x, y, w, h = face
    ox, oy = roi_offset

    num_labels, labels, stats, centroids = cv2.connectedComponentsWithStats(
        mask,
        connectivity=8
    )

    if num_labels <= 1:
        return None

    # Titik acuan: bagian atas wajah.
    anchor_x = (x + w / 2) - ox
    anchor_y = (y + h * 0.20) - oy

    best_label = None
    best_score = -1e9

    for label in range(1, num_labels):
        lx = stats[label, cv2.CC_STAT_LEFT]
        ly = stats[label, cv2.CC_STAT_TOP]
        lw = stats[label, cv2.CC_STAT_WIDTH]
        lh = stats[label, cv2.CC_STAT_HEIGHT]
        area = stats[label, cv2.CC_STAT_AREA]

        if area < max(30, w * h * 0.015):
            continue

        # Apakah komponen mencakup area wajah?
        contains_face = (
            lx <= anchor_x <= lx + lw and
            ly <= anchor_y <= ly + lh
        )

        # Jarak centroid ke anchor wajah.
        cx = centroids[label][0]
        cy = centroids[label][1]

        dist = np.hypot(
            (cx - anchor_x) / max(w, 1),
            (cy - anchor_y) / max(h, 1)
        )

        score = area / max(w * h, 1) * 3
        score -= dist * 2

        if contains_face:
            score += 5

        if score > best_score:
            best_score = score
            best_label = label

    if best_label is None:
        return None

    return (labels == best_label).astype(np.uint8)


def grabcut_head(image, face):
    """
    Menjalankan GrabCut hanya pada area kepala.

    Inisialisasi:
    - wajah = definite/probable foreground
    - area rambut/jilbab di atas wajah = probable foreground
    - area luar kepala = background

    Dengan cara ini kita mencari SILUET kepala, bukan sekadar kotak wajah.
    """

    H, W = image.shape[:2]
    x, y, w, h = face

    cx = x + w / 2

    roi_left = max(
        0,
        int(cx - w * HEAD_ROI_WIDTH / 2)
    )

    roi_right = min(
        W,
        int(cx + w * HEAD_ROI_WIDTH / 2)
    )

    roi_top = max(
        0,
        int(y - h * HEAD_ROI_ABOVE)
    )

    roi_bottom = min(
        H,
        int(y + h * HEAD_ROI_BELOW)
    )

    if roi_right - roi_left < 40:
        return None

    if roi_bottom - roi_top < 40:
        return None

    roi = image[
        roi_top:roi_bottom,
        roi_left:roi_right
    ].copy()

    rh, rw = roi.shape[:2]

    # --------------------------------------------------------
    # GrabCut mask
    # --------------------------------------------------------
    gc_mask = np.full(
        (rh, rw),
        cv2.GC_BGD,
        dtype=np.uint8
    )

    # Bagian tengah area kepala = probable foreground.
    # Ini mencakup rambut/jilbab + wajah.
    center_x = int((x + w / 2) - roi_left)

    pf_left = max(0, int(center_x - w * 0.95))
    pf_right = min(rw, int(center_x + w * 0.95))

    pf_top = max(0, int(y - h * 1.20) - roi_top)
    pf_bottom = min(rh, int(y + h * 0.30) - roi_top)

    if pf_right > pf_left and pf_bottom > pf_top:
        gc_mask[pf_top:pf_bottom, pf_left:pf_right] = cv2.GC_PR_FGD

    # Wajah sendiri dibuat definite foreground.
    fx1 = max(0, int(x - roi_left + w * 0.05))
    fx2 = min(rw, int(x - roi_left + w * 0.95))
    fy1 = max(0, int(y - roi_top + h * 0.05))
    fy2 = min(rh, int(y - roi_top + h * 0.95))

    if fx2 > fx1 and fy2 > fy1:
        gc_mask[fy1:fy2, fx1:fx2] = cv2.GC_FGD

    # Strip tipis di sisi ROI dipastikan background.
    gc_mask[:2, :] = cv2.GC_BGD
    gc_mask[-2:, :] = cv2.GC_BGD
    gc_mask[:, :2] = cv2.GC_BGD
    gc_mask[:, -2:] = cv2.GC_BGD

    bgd_model = np.zeros((1, 65), np.float64)
    fgd_model = np.zeros((1, 65), np.float64)

    try:
        cv2.grabCut(
            roi,
            gc_mask,
            None,
            bgd_model,
            fgd_model,
            7,
            cv2.GC_INIT_WITH_MASK
        )
    except cv2.error:
        return None

    foreground = np.where(
        (gc_mask == cv2.GC_FGD) |
        (gc_mask == cv2.GC_PR_FGD),
        1,
        0
    ).astype(np.uint8)

    foreground = clean_mask(foreground)

    # Ambil komponen yang berhubungan dengan wajah.
    component = largest_component_near_face(
        foreground,
        face,
        (roi_left, roi_top)
    )

    if component is None:
        return None

    return component, (roi_left, roi_top)


def cari_tepi_atas_kepala(image, face):
    """
    Cari baris paling atas dari siluet rambut/jilbab.

    Tidak mengambil titik paling atas secara membabi buta.
    Kita mencari beberapa baris foreground yang saling mendukung.
    """

    result = grabcut_head(image, face)

    if result is None:
        return None, 0.0, None

    component, (ox, oy) = result

    H, W = component.shape
    x, y, w, h = face

    # Batasi pencarian hanya di atas sampai sedikit di bawah dahi.
    local_face_y = y - oy

    search_bottom = min(
        H,
        max(0, int(local_face_y + h * 0.15))
    )

    if search_bottom <= 2:
        return None, 0.0, component

    # Kepala harus berada kira-kira di area tengah wajah.
    center_x = int((x + w / 2) - ox)

    left = max(0, int(center_x - w * 1.25))
    right = min(W, int(center_x + w * 1.25))

    candidates = []

    min_span = max(
        8,
        int(w * MIN_HEAD_WIDTH_RATIO)
    )

    for yy in range(search_bottom):
        row = component[yy, left:right]

        xs = np.where(row > 0)[0]

        if xs.size < min_span:
            continue

        span = int(np.ptp(xs) + 1)

        if span < min_span:
            continue

        # Harus ada foreground pada beberapa baris setelahnya.
        support = 0

        for yy2 in range(
            yy,
            min(search_bottom, yy + MIN_SUPPORT_ROWS)
        ):
            xs2 = np.where(component[yy2, left:right] > 0)[0]

            if xs2.size >= max(4, int(min_span * 0.35)):
                support += 1

        if support >= MIN_SUPPORT_ROWS:
            candidates.append(yy)

    if not candidates:
        return None, 0.0, component

    top_local = candidates[0]
    top_global = oy + top_local

    # Validasi: kepala harus berada di atas wajah.
    distance = y - top_global

    if distance < h * 0.12:
        return None, 0.0, component

    # Jangan menerima hasil yang absurd terlalu jauh dari wajah.
    if distance > h * 1.50:
        return None, 0.0, component

    xs = np.where(
        component[top_local, left:right] > 0
    )[0]

    span = int(np.ptp(xs) + 1) if xs.size else 0

    confidence = min(
        1.0,
        span / max(1.0, w * 0.75)
    )

    return int(top_global), float(confidence), component


# ============================================================
# UKURAN CROP
# ============================================================

def tentukan_crop_size(image_w, image_h, face):
    """
    Tentukan ukuran crop berdasarkan lebar wajah.

    Jika wajah sumber besar -> crop lebih besar.
    Jika wajah sumber kecil -> crop lebih kecil.

    Tujuannya agar ukuran wajah hasil relatif seragam.
    """

    x, y, fw, fh = face

    target = float(TARGET_FACE_W)

    target = max(
        MIN_OUTPUT_FACE_W,
        min(MAX_OUTPUT_FACE_W, target)
    )

    crop_w = fw * OUTPUT_W / target
    crop_h = crop_w * OUTPUT_H / OUTPUT_W

    # Ukuran crop 3:4 maksimal yang tersedia dari sumber.
    max_crop_w = min(
        float(image_w),
        float(image_h) * OUTPUT_W / OUTPUT_H
    )

    max_crop_h = max_crop_w * OUTPUT_H / OUTPUT_W

    if crop_w > max_crop_w:
        crop_w = max_crop_w
        crop_h = max_crop_h

    return crop_w, crop_h


# ============================================================
# HITUNG AREA CROP
# ============================================================

def hitung_crop(image_w, image_h, face, top_head):
    x, y, fw, fh = map(float, face)

    face_cx = x + fw / 2
    face_cy = y + fh / 2

    crop_w, crop_h = tentukan_crop_size(
        image_w,
        image_h,
        face
    )

    # --------------------------------------------------------
    # Horizontal
    # --------------------------------------------------------
    left = face_cx - crop_w / 2

    left = max(
        0,
        min(left, image_w - crop_w)
    )

    # --------------------------------------------------------
    # Vertical
    # --------------------------------------------------------
    top_margin = crop_h * TOP_MARGIN_RATIO

    # RAMBUT/JILBAB adalah anchor utama.
    top_from_head = top_head - top_margin

    # Posisi wajah hanya digunakan sebagai anchor kedua.
    desired_face_center = crop_h * FACE_CENTER_Y

    top_from_face = face_cy - desired_face_center

    # Kepala sangat dominan.
    top = (
        top_from_head * 0.90 +
        top_from_face * 0.10
    )

    # Pastikan kepala tidak pernah masuk ke luar frame crop.
    top = min(
        top,
        top_head - top_margin
    )

    # --------------------------------------------------------
    # Jika bagian bawah crop keluar foto, geser ke atas.
    # Tetapi jangan sampai rambut terpotong.
    # --------------------------------------------------------
    max_top = image_h - crop_h

    if top > max_top:
        top = max_top

        # Jika menggeser ke atas membuat kepala terpotong,
        # crop tidak mungkin memenuhi keduanya dengan ukuran ini.
        # Dalam kondisi tersebut ukuran crop akan diperkecil.
        if top > top_head - top_margin:
            crop_h2 = top_head - top_margin + crop_h

            crop_w2 = crop_h2 * OUTPUT_W / OUTPUT_H

            min_crop_w = fw * OUTPUT_W / MAX_OUTPUT_FACE_W

            if crop_w2 >= min_crop_w:
                crop_w = crop_w2
                crop_h = crop_h2

                left = face_cx - crop_w / 2
                left = max(
                    0,
                    min(left, image_w - crop_w)
                )

                max_top = image_h - crop_h
                top = min(
                    top_head - crop_h * TOP_MARGIN_RATIO,
                    max_top
                )

    top = max(
        0,
        min(top, image_h - crop_h)
    )

    right = left + crop_w
    bottom = top + crop_h

    return (
        int(round(left)),
        int(round(top)),
        int(round(right)),
        int(round(bottom))
    )


# ============================================================
# CROP + RESIZE
# ============================================================

def crop_resize(image, box):
    left, top, right, bottom = box

    cropped = image[
        top:bottom,
        left:right
    ]

    if cropped.size == 0:
        return None

    return cv2.resize(
        cropped,
        (OUTPUT_W, OUTPUT_H),
        interpolation=cv2.INTER_LANCZOS4
    )


# ============================================================
# PREVIEW
# ============================================================

def buat_preview(
    image,
    face,
    top_head,
    crop_box,
    confidence
):
    preview = image.copy()

    x, y, w, h = face
    left, top, right, bottom = crop_box

    # BIRU = wajah
    cv2.rectangle(
        preview,
        (x, y),
        (x + w, y + h),
        (255, 0, 0),
        3
    )

    # MERAH = tepi atas rambut/jilbab
    cv2.line(
        preview,
        (0, int(top_head)),
        (preview.shape[1], int(top_head)),
        (0, 0, 255),
        6
    )

    cv2.circle(
        preview,
        (int(x + w / 2), int(top_head)),
        10,
        (0, 0, 255),
        -1
    )

    # HIJAU = crop
    cv2.rectangle(
        preview,
        (left, top),
        (right, bottom),
        (0, 255, 0),
        5
    )

    # KUNING = tengah crop
    center_x = (left + right) // 2

    cv2.line(
        preview,
        (center_x, top),
        (center_x, bottom),
        (0, 255, 255),
        2
    )

    info1 = f"HEAD TOP = {top_head}px | CONF = {confidence:.2f}"
    info2 = f"FACE = {w}px | CROP = {right-left}x{bottom-top}"

    for text, yy in (
        (info1, 35),
        (info2, 70)
    ):
        cv2.putText(
            preview,
            text,
            (15, yy),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.70,
            (255, 255, 255),
            4,
            cv2.LINE_AA
        )

        cv2.putText(
            preview,
            text,
            (15, yy),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.70,
            (0, 0, 0),
            1,
            cv2.LINE_AA
        )

    return preview


# ============================================================
# MAIN
# ============================================================

def main():
    print("=" * 70)
    print(" AUTO CROP PASFOTO - V4 FINAL")
    print("=" * 70)
    print("Output       : 300 x 400 px")
    print("Patokan atas : TEPI RAMBUT / JILBAB")
    print("Metode       : OpenCV GrabCut")
    print(f"Target wajah : {TARGET_FACE_W}px")
    print()

    folder = pilih_folder()

    if not folder:
        print("Folder tidak dipilih.")
        return

    input_folder = Path(folder)

    output_folder = input_folder / "hasil_crop"
    preview_folder = input_folder / "preview"
    failed_folder = input_folder / "perlu_diperiksa"

    output_folder.mkdir(exist_ok=True)
    preview_folder.mkdir(exist_ok=True)
    failed_folder.mkdir(exist_ok=True)

    try:
        cascade = load_face_cascade()
    except Exception as e:
        messagebox.showerror(
            "Error",
            str(e)
        )
        return

    files = sorted(
        [
            p for p in input_folder.iterdir()
            if p.is_file()
            and p.suffix.lower() in IMAGE_EXTENSIONS
        ],
        key=lambda p: p.name.lower()
    )

    if not files:
        messagebox.showwarning(
            "Tidak ada foto",
            "Tidak ditemukan file gambar."
        )
        return

    berhasil = 0
    gagal = 0

    print(f"Jumlah foto : {len(files)}")
    print()

    for no, path in enumerate(files, 1):
        print(
            f"[{no}/{len(files)}] {path.name}",
            end=" ... "
        )

        try:
            image = baca_gambar(path)

            if image is None:
                raise RuntimeError("gambar tidak dapat dibaca")

            H, W = image.shape[:2]

            faces = detect_faces(
                image,
                cascade
            )

            if len(faces) == 0:
                raise RuntimeError(
                    "wajah tidak terdeteksi"
                )

            face = pilih_wajah(
                faces,
                image
            )

            if face is None:
                raise RuntimeError(
                    "wajah tidak valid"
                )

            # ------------------------------------------------
            # DETEKSI TEPI RAMBUT / JILBAB
            # ------------------------------------------------
            top_head, confidence, debug_mask = cari_tepi_atas_kepala(
                image,
                face
            )

            if top_head is None:
                raise RuntimeError(
                    "tepi rambut/jilbab tidak berhasil dideteksi"
                )

            crop_box = hitung_crop(
                W,
                H,
                face,
                top_head
            )

            result = crop_resize(
                image,
                crop_box
            )

            if result is None:
                raise RuntimeError(
                    "crop gagal"
                )

            output_path = output_folder / (
                path.stem + ".jpg"
            )

            if not simpan_gambar(
                output_path,
                result
            ):
                raise RuntimeError(
                    "gagal menyimpan hasil"
                )

            # Preview dengan:
            # biru = wajah
            # merah = rambut/jilbab
            # hijau = crop
            # kuning = tengah
            preview = buat_preview(
                image,
                face,
                top_head,
                crop_box,
                confidence
            )

            preview_path = preview_folder / (
                path.stem + ".jpg"
            )

            simpan_gambar(
                preview_path,
                preview
            )

            print(
                f"OK | top={top_head} | conf={confidence:.2f}"
            )

            berhasil += 1

        except Exception as e:
            print(
                f"GAGAL | {e}"
            )

            try:
                destination = failed_folder / path.name

                if destination.exists():
                    destination.unlink()

                shutil.copy2(
                    path,
                    destination
                )

            except Exception:
                pass

            gagal += 1

    print()
    print("=" * 70)
    print(" SELESAI")
    print("=" * 70)
    print(f"Berhasil        : {berhasil}")
    print(f"Perlu diperiksa : {gagal}")
    print()
    print(f"Hasil crop : {output_folder}")
    print(f"Preview    : {preview_folder}")
    print(f"Gagal      : {failed_folder}")
    print("=" * 70)

    root = tk.Tk()
    root.withdraw()
    root.attributes("-topmost", True)

    messagebox.showinfo(
        "Selesai",
        "Proses crop selesai.\n\n"
        f"Berhasil        : {berhasil}\n"
        f"Perlu diperiksa : {gagal}\n\n"
        f"Hasil:\n{output_folder}"
    )

    root.destroy()


if __name__ == "__main__":
    main()
