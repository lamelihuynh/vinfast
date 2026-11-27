#!/usr/bin/env python3
"""
bench/t1/report/plot.py — Vẽ 3 biểu đồ PNG cho Topic T1 (T1-22) từ dữ liệu thực nghiệm CSV:
  1. bench/t1/results/plots/t1_detail_read.png   (Thí nghiệm A: cột nhóm theo size, chiều cao p50, vạch lỗi tới p95)
  2. bench/t1/results/plots/t1_add_attribute.png (Thí nghiệm B: cột theo biến thể, trục y log, 2 màu cho 10k/100k)
  3. bench/t1/results/plots/t1_reviews.png       (Thí nghiệm C: 2 ô — p50 theo số review trục x log & kích thước BSON kèm trần 16 MB)

Chạy trên máy host:
  python bench/t1/report/plot.py
"""

from __future__ import annotations

import csv
import math
from pathlib import Path
from typing import Dict, List, Tuple

import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
import matplotlib.ticker as ticker

T1_DIR = Path(__file__).resolve().parents[1]
ROOT = T1_DIR.parents[1]
RESULTS_DIR = T1_DIR / "results"
PLOTS_DIR = RESULTS_DIR / "plots"
SUMMARY_CSV = RESULTS_DIR / "t1_summary_all.csv"
REVIEWS_SIZE_CSV = RESULTS_DIR / "raw" / "t1_reviews_size_10k.csv"

plt.rcParams.update({
    "font.sans-serif": ["Segoe UI", "DejaVu Sans", "Arial", "sans-serif"],
    "axes.unicode_minus": False,
    "figure.dpi": 160,
    "savefig.dpi": 200,
    "axes.grid": True,
    "grid.alpha": 0.28,
    "grid.linestyle": "--",
    "axes.spines.top": False,
    "axes.spines.right": False,
})


def load_summary() -> List[Dict[str, str]]:
    if not SUMMARY_CSV.exists():
        raise FileNotFoundError(f"Chưa có {SUMMARY_CSV}. Hãy chạy bench/t1/report/summarize.php trước.")
    with SUMMARY_CSV.open("r", encoding="utf-8", newline="") as f:
        return list(csv.DictReader(f))


def load_review_sizes() -> Dict[int, Tuple[float, float]]:
    """Trả về {level: (avg_bson_bytes, avg_pg_bytes_per_product)}."""
    if not REVIEWS_SIZE_CSV.exists():
        raise FileNotFoundError(f"Chưa có {REVIEWS_SIZE_CSV}.")
    buckets: Dict[int, List[Tuple[int, int]]] = {}
    with REVIEWS_SIZE_CSV.open("r", encoding="utf-8", newline="") as f:
        for r in csv.DictReader(f):
            lv = int(r["level"])
            buckets.setdefault(lv, []).append((int(r["bson_bytes"]), int(r["pg_bytes_per_product"])))
    out: Dict[int, Tuple[float, float]] = {}
    for lv, pairs in sorted(buckets.items()):
        avg_bson = sum(p[0] for p in pairs) / len(pairs)
        avg_pg = sum(p[1] for p in pairs) / len(pairs)
        out[lv] = (avg_bson, avg_pg)
    return out


def plot_detail_read(rows: List[Dict[str, str]]) -> Path:
    """Thí nghiệm A: cột nhóm theo size (10k, 100k), chiều cao p50, vạch lỗi tới p95."""
    exp_rows = [r for r in rows if r["experiment"] == "detail_read"]
    sizes = ["10k", "100k"]
    variants = [
        ("mongo_findone", "Mongo findOne (1 doc)", "#10aa50"),
        ("sql_join4", "PG JOIN 4 bảng (ghép PHP)", "#2563eb"),
        ("sql_json_agg", "PG json_agg (1 dòng)", "#7c3aed"),
    ]

    lookup = {(r["dataset_size"], r["variant"]): r for r in exp_rows}

    fig, ax = plt.subplots(figsize=(8.6, 5.2))
    x_centers = [0.0, 1.0]
    width = 0.24
    offsets = [-width, 0.0, width]

    max_y = 0.0
    for idx, (v_key, v_label, color) in enumerate(variants):
        xs = [xc + offsets[idx] for xc in x_centers]
        p50s = [float(lookup[(sz, v_key)]["p50_ms"]) for sz in sizes]
        p95s = [float(lookup[(sz, v_key)]["p95_ms"]) for sz in sizes]
        upper_err = [max(0.0, p95 - p50) for p50, p95 in zip(p50s, p95s)]
        max_y = max(max_y, max(p95s))

        bars = ax.bar(
            xs,
            p50s,
            width=width * 0.9,
            label=v_label,
            color=color,
            alpha=0.9,
            edgecolor="#1e293b",
            linewidth=0.6,
            yerr=[[0.0] * len(xs), upper_err],
            capsize=5,
            error_kw={"elinewidth": 1.3, "ecolor": "#0f172a", "capthick": 1.3},
            zorder=3,
        )

        for bx, p50, p95 in zip(bars, p50s, p95s):
            cx = bx.get_x() + bx.get_width() / 2
            ax.text(
                cx,
                p50 / 2,
                f"p50\n{p50:.2f}",
                ha="center",
                va="center",
                fontsize=8.2,
                fontweight="bold",
                color="white",
                zorder=4,
            )
            ax.text(
                cx,
                p95 + max_y * 0.025,
                f"p95: {p95:.2f}",
                ha="center",
                va="bottom",
                fontsize=8.0,
                color="#0f172a",
                zorder=4,
            )

    ax.set_xticks(x_centers)
    ax.set_xticklabels(["10.000 sản phẩm (10k)", "100.000 sản phẩm (100k)"], fontsize=10.5, fontweight="bold")
    ax.set_ylabel("Độ trễ đọc trọn 1 sản phẩm (ms)", fontsize=10.5)
    ax.set_title(
        "Thí nghiệm A (T1-19): Đọc chi tiết 1 sản phẩm — p50 (cột) và p95 (vạch lỗi)",
        fontsize=12,
        fontweight="bold",
        pad=14,
    )
    ax.set_ylim(0, max_y * 1.24)
    ax.legend(frameon=True, facecolor="white", edgecolor="#cbd5e1", loc="upper left", fontsize=9.2)

    PLOTS_DIR.mkdir(parents=True, exist_ok=True)
    out = PLOTS_DIR / "t1_detail_read.png"
    fig.tight_layout()
    fig.savefig(out)
    plt.close(fig)
    return out


def plot_add_attribute(rows: List[Dict[str, str]]) -> Path:
    """Thí nghiệm B: cột theo biến thể, trục y log, 2 màu cho 10k / 100k."""
    exp_rows = [r for r in rows if r["experiment"] == "add_attribute"]
    variants = [
        ("mongo_category_only", "Mongo\ncategory_only"),
        ("pg_alter_default", "PG ALTER\nDEFAULT const"),
        ("pg_eav", "PG EAV\nINSERT..SELECT"),
        ("pg_jsonb", "PG JSONB\nUPDATE attrs"),
        ("mongo_backfill", "Mongo\nupdateMany"),
        ("pg_alter_backfill", "PG ALTER\n+ UPDATE backfill"),
    ]
    lookup = {(r["dataset_size"], r["variant"]): r for r in exp_rows}

    fig, ax = plt.subplots(figsize=(10.2, 5.6))
    xs = list(range(len(variants)))
    width = 0.36

    size_styles = [
        ("10k", "10k sản phẩm", "#38bdf8", -width / 2),
        ("100k", "100k sản phẩm", "#1d4ed8", width / 2),
    ]

    for sz, label, color, offset in size_styles:
        pos = [x + offset for x in xs]
        p50s = [float(lookup[(sz, vk)]["p50_ms"]) for vk, _ in variants]
        p95s = [float(lookup[(sz, vk)]["p95_ms"]) for vk, _ in variants]
        upper_err = [max(0.0, p95 - p50) for p50, p95 in zip(p50s, p95s)]

        bars = ax.bar(
            pos,
            p50s,
            width=width * 0.92,
            label=label,
            color=color,
            edgecolor="#0f172a",
            linewidth=0.6,
            yerr=[[0.0] * len(pos), upper_err],
            capsize=4,
            error_kw={"elinewidth": 1.0, "ecolor": "#334155"},
            zorder=3,
        )

        for bx, p50, p95 in zip(bars, p50s, p95s):
            cx = bx.get_x() + bx.get_width() / 2
            val_txt = f"{p50:.1f}" if p50 < 100 else f"{p50:.0f}"
            ax.text(
                cx,
                p95 * 1.14,
                val_txt,
                ha="center",
                va="bottom",
                fontsize=8.0,
                fontweight="bold",
                color="#0f172a",
            )

    blocked_100k = float(lookup[("100k", "pg_alter_backfill")]["param"])
    p95_backfill_100k = float(lookup[("100k", "pg_alter_backfill")]["p95_ms"])
    ax.annotate(
        f"AccessExclusiveLock chặn SELECT\np50 ≈ {blocked_100k:,.0f} ms ở 100k",
        xy=(len(variants) - 1 + width / 2, p95_backfill_100k),
        xytext=(len(variants) - 2.55, p95_backfill_100k * 1.65),
        fontsize=8.6,
        fontweight="bold",
        color="#b91c1c",
        bbox={"boxstyle": "round,pad=0.35", "facecolor": "#fef2f2", "edgecolor": "#ef4444"},
        arrowprops={"arrowstyle": "->", "color": "#dc2626", "lw": 1.2},
    )

    ax.set_yscale("log")
    ax.set_xticks(xs)
    ax.set_xticklabels([lbl for _, lbl in variants], fontsize=9.5)
    ax.set_ylabel("Thời gian thực thi p50 + vạch p95 (ms, thang log)", fontsize=10.5)
    ax.set_title(
        "Thí nghiệm B (T1-20): Chi phí thêm 1 thuộc tính động ở 10k vs 100k sản phẩm",
        fontsize=12,
        fontweight="bold",
        pad=14,
    )
    ax.set_ylim(0.8, p95_backfill_100k * 5.0)
    ax.yaxis.set_major_formatter(ticker.ScalarFormatter())
    ax.legend(frameon=True, facecolor="white", edgecolor="#cbd5e1", loc="upper left", fontsize=9.5)

    PLOTS_DIR.mkdir(parents=True, exist_ok=True)
    out = PLOTS_DIR / "t1_add_attribute.png"
    fig.tight_layout()
    fig.savefig(out)
    plt.close(fig)
    return out


def plot_reviews(rows: List[Dict[str, str]], size_map: Dict[int, Tuple[float, float]]) -> Path:
    """Thí nghiệm C: 2 ô — (trái) p50 theo số review trục x log; (phải) kích thước BSON & đường trần 16 MB."""
    exp_rows = [r for r in rows if r["experiment"] == "reviews"]
    levels = sorted({int(r["param"]) for r in exp_rows})
    lookup = {(r["variant"], int(r["param"])): float(r["p50_ms"]) for r in exp_rows}

    series = [
        ("embedded_write_one", "Mongo $push đầu mảng (ghi 1 bài)", "#dc2626", "o", "-", 2.2),
        ("sql_write_one", "PG INSERT (ghi 1 bài)", "#2563eb", "s", "--", 2.0),
        ("embedded_read_mid", "Mongo $slice giữa mảng (đọc trang giữa)", "#ea580c", "^", "-", 1.9),
        ("sql_offset_mid", "PG OFFSET L/2 (đọc trang giữa)", "#7c3aed", "D", "-.", 1.7),
        ("sql_keyset_mid", "PG Keyset cursor (đọc trang giữa)", "#0d9488", "v", "--", 2.0),
        ("embedded_read_page1", "Mongo $slice [0,10] (trang 1)", "#f59e0b", "P", ":", 1.7),
        ("hybrid_page1", "Hybrid Mongo 5 bài nhúng (app thật)", "#16a34a", "*", "-", 2.4),
    ]

    fig, (ax1, ax2) = plt.subplots(1, 2, figsize=(13.2, 5.5))

    # --- Ô 1: p50 latency vs số review (trục X log) ---
    end_points = []
    for v_key, label, color, marker, ls, lw in series:
        ys = [lookup[(v_key, lv)] for lv in levels]
        ax1.plot(levels, ys, label=label, color=color, marker=marker, linestyle=ls, linewidth=lw, markersize=6.5)
        end_points.append((ys[-1], f"{ys[-1]:.2f} ms", color))

    # Tách nhẹ nhãn bên phải khi hai đường có giá trị cuối gần nhau (VD: 3.42 ms và 3.22 ms)
    end_points.sort(key=lambda item: item[0])
    min_gap = 0.95
    placed_ys: List[float] = []
    for raw_y, txt, color in end_points:
        adj_y = raw_y
        if placed_ys and adj_y - placed_ys[-1] < min_gap:
            adj_y = placed_ys[-1] + min_gap
        placed_ys.append(adj_y)
        ax1.annotate(
            txt,
            xy=(levels[-1], raw_y),
            xytext=(levels[-1] * 1.12, adj_y),
            va="center",
            fontsize=7.8,
            color=color,
            fontweight="bold",
        )

    ax1.set_xscale("log")
    ax1.set_xticks(levels)
    ax1.set_xticklabels([f"{lv:,}".replace(",", ".") for lv in levels], fontsize=9.5)
    ax1.set_xlim(levels[0] * 0.75, levels[-1] * 2.3)
    ax1.set_xlabel("Số đánh giá / sản phẩm (trục log)", fontsize=10)
    ax1.set_ylabel("Độ trễ p50 (ms)", fontsize=10)
    ax1.set_title("(a) Độ trễ p50 đọc / ghi / phân trang theo số review", fontsize=11, fontweight="bold")
    ax1.legend(frameon=True, facecolor="white", edgecolor="#cbd5e1", loc="upper left", fontsize=8.1)

    # --- Ô 2: Kích thước document BSON theo level + đường giới hạn 16 MB ---
    lv_sorted = sorted(size_map.keys())
    bson_mb = [size_map[lv][0] / (1024 * 1024) for lv in lv_sorted]
    pg_mb = [size_map[lv][1] / (1024 * 1024) for lv in lv_sorted]

    # Ngoại suy tuyến tính tới ngưỡng 16 MB BSON
    min_lv, max_lv = lv_sorted[0], lv_sorted[-1]
    bytes_per_rev = (size_map[max_lv][0] - size_map[min_lv][0]) / (max_lv - min_lv)
    base_bytes = size_map[min_lv][0] - bytes_per_rev * min_lv
    limit_16mb_bytes = 16 * 1024 * 1024
    cutoff_reviews = int(math.floor((limit_16mb_bytes - base_bytes) / bytes_per_rev))

    extrap_x = [max_lv, cutoff_reviews]
    extrap_y = [size_map[max_lv][0] / (1024 * 1024), 16.0]

    ax2.plot(lv_sorted, bson_mb, color="#dc2626", marker="o", linewidth=2.2, markersize=7, label="Mongo nhúng toàn bộ ($bsonSize)")
    ax2.plot(lv_sorted, pg_mb, color="#2563eb", marker="s", linestyle="--", linewidth=1.8, markersize=6, label="PG bảng riêng (relation / SP)")
    ax2.plot(extrap_x, extrap_y, color="#dc2626", linestyle=":", linewidth=1.8, label=f"Ngoại suy chạm 16 MB (~{cutoff_reviews:,} bài)".replace(",", "."))
    ax2.scatter([cutoff_reviews], [16.0], color="#b91c1c", s=65, zorder=5)

    ax2.axhline(16.0, color="#991b1b", linestyle="--", linewidth=1.6, label="Giới hạn cứng BSON 16 MB")
    ax2.axhline(1.5 / 1024, color="#16a34a", linestyle="-.", linewidth=1.6, label="Hybrid Subset (tối đa 5 bài ≈ 1.5 KB)")

    for idx, (lv, b_mb) in enumerate(zip(lv_sorted, bson_mb)):
        lbl = f"{size_map[lv][0] / 1024:.1f} KB" if b_mb < 0.01 else (f"{size_map[lv][0] / 1024:.0f} KB" if b_mb < 1.0 else f"{b_mb:.2f} MB")
        y_off = -14 if idx == 0 else 8
        ax2.annotate(
            lbl,
            xy=(lv, b_mb),
            xytext=(0, y_off),
            textcoords="offset points",
            ha="center",
            fontsize=8.2,
            fontweight="bold",
            color="#991b1b",
        )

    ax2.annotate(
        f"Chạm trần 16 MB\n≈ {cutoff_reviews:,} reviews/SP".replace(",", "."),
        xy=(cutoff_reviews, 16.0),
        xytext=(cutoff_reviews * 0.16, 18.5),
        fontsize=8.6,
        fontweight="bold",
        color="#991b1b",
        bbox={"boxstyle": "round,pad=0.3", "facecolor": "#fef2f2", "edgecolor": "#ef4444"},
        arrowprops={"arrowstyle": "->", "color": "#b91c1c", "lw": 1.2},
    )

    ax2.set_xscale("log")
    ax2.set_yscale("log")
    ax2.set_xlim(7, cutoff_reviews * 1.6)
    ax2.set_ylim(0.0008, 40.0)
    ax2.set_xlabel("Số đánh giá / sản phẩm (trục log)", fontsize=10)
    ax2.set_ylabel("Dung lượng lưu trữ / sản phẩm (MB, trục log)", fontsize=10)
    ax2.set_title("(b) Tăng trưởng kích thước Document BSON & ngưỡng 16 MB", fontsize=11, fontweight="bold")
    ax2.legend(frameon=True, facecolor="white", edgecolor="#cbd5e1", loc="lower right", fontsize=8.0)

    fig.suptitle(
        "Thí nghiệm C (T1-21): Review nhúng toàn bộ vs Bảng SQL vs Phương án Lai (Subset Pattern)",
        fontsize=12.5,
        fontweight="bold",
        y=1.01,
    )
    PLOTS_DIR.mkdir(parents=True, exist_ok=True)
    out = PLOTS_DIR / "t1_reviews.png"
    fig.tight_layout()
    fig.savefig(out, bbox_inches="tight")
    plt.close(fig)
    return out


def main() -> None:
    rows = load_summary()
    size_map = load_review_sizes()
    p1 = plot_detail_read(rows)
    p2 = plot_add_attribute(rows)
    p3 = plot_reviews(rows, size_map)
    print("Generated charts:")
    for p in (p1, p2, p3):
        print(f"  - {p.relative_to(ROOT)} ({p.stat().st_size:,} bytes)")


if __name__ == "__main__":
    main()
