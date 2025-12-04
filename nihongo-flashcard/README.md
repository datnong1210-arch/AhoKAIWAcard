# Nihongo Flashcard - Học tiếng Nhật

Plugin WordPress học tiếng Nhật dành cho người Việt với hệ thống Flashcard chuyên nghiệp, đẹp mắt.

## Tính năng

### 🎴 Giao diện Flashcard đẹp mắt
- Card thiết kế 1 mặt với 2 phần trên/dưới ngăn cách bởi đường line ngang
- Phần trên: Hiển thị từ vựng tiếng Nhật (Kanji/Hiragana/Katakana)
- Phần dưới: Hiển thị nghĩa tiếng Việt, phiên âm, ví dụ
- Icons hóa các công cụ tiện lợi (next, previous, shuffle, flip, audio, progress)
- Responsive design hoạt động tốt trên mobile/tablet/desktop
- Animation mượt mà khi chuyển thẻ
- Màu sắc hiện đại, phong cách Nhật Bản

### 🎵 Audio Player chuyên nghiệp
- Mỗi bộ thẻ (deck) có thể thêm URL audio (MP3, WAV)
- Custom audio player xịn xò hiển thị phía trên bộ thẻ:
  - Nút Play/Pause với icon đẹp
  - Progress bar có thể kéo thả
  - Hiển thị thời gian hiện tại / tổng thời gian
  - Volume control
  - Thiết kế tối giản, đẹp mắt phù hợp phong cách Nhật

### 📊 Import/Export CSV
- Export CSV: Xuất từng bộ thẻ ra file CSV
- Import CSV: Nhập bộ thẻ từ file CSV
- Encoding UTF-8 BOM để không bị lỗi font tiếng Nhật, tiếng Việt
- Template CSV mẫu để người dùng download và điền
- Cấu trúc CSV: `front_top, front_bottom, audio_url (optional)`

### 📚 Đại bộ thẻ (Course System)
- Cho phép gom nhiều bộ thẻ vào 1 "Đại bộ thẻ" (Course/Khóa học)
- Shortcode cho từng khóa học: `[nihongo_course id="X"]`
- Shortcode cho từng bộ thẻ: `[nihongo_deck id="X"]`
- Grid layout đẹp mắt hiển thị các bộ thẻ trong khóa học
- Navigation giữa các bộ thẻ trong khóa học

### 🎯 Tính năng bổ sung
- Lưu tiến độ học cho từng user
- Shuffle mode để học ngẫu nhiên
- Keyboard navigation (arrow keys, space)
- Touch/swipe support cho mobile
- Dark mode support (tự động theo hệ thống)

## Cài đặt

1. Tải plugin về máy
2. Upload thư mục `nihongo-flashcard` vào `/wp-content/plugins/`
3. Kích hoạt plugin trong WordPress Admin > Plugins
4. Truy cập menu "Nihongo Flashcard" để bắt đầu tạo bộ thẻ

## Sử dụng

### Tạo bộ thẻ mới

1. Vào **Nihongo Flashcard > Bộ thẻ > Thêm mới**
2. Nhập tên bộ thẻ
3. (Tùy chọn) Thêm URL audio cho bộ thẻ
4. Thêm các thẻ từ vựng:
   - **Phần trên**: Từ vựng tiếng Nhật
   - **Phần dưới**: Nghĩa, phiên âm, ví dụ
   - **Audio riêng**: URL audio cho từng thẻ (tùy chọn)
5. Lưu bộ thẻ

### Tạo khóa học

1. Vào **Nihongo Flashcard > Khóa học > Thêm mới**
2. Nhập tên khóa học
3. Chọn các bộ thẻ muốn đưa vào khóa học
4. Kéo thả để sắp xếp thứ tự
5. Lưu khóa học

### Sử dụng Shortcode

```
[nihongo_deck id="123"]     - Hiển thị bộ thẻ ID 123
[nihongo_course id="456"]   - Hiển thị khóa học ID 456
```

### Phím tắt

| Phím | Chức năng |
|------|-----------|
| `←`  | Thẻ trước |
| `→`  | Thẻ sau |
| `Space` | Lật thẻ |
| `S` | Xáo trộn |

### Import/Export CSV

**Cấu trúc file CSV:**

| Cột | Mô tả | Bắt buộc |
|-----|-------|----------|
| `front_top` | Phần trên thẻ (tiếng Nhật) | Có |
| `front_bottom` | Phần dưới thẻ (nghĩa, phiên âm) | Có |
| `audio_url` | URL audio cho thẻ | Không |

**Ví dụ:**
```csv
front_top,front_bottom,audio_url
日本語,Tiếng Nhật / にほんご / Nihongo,
勉強,Học tập / べんきょう / Benkyou,https://example.com/audio.mp3
こんにちは,Xin chào / Konnichiwa,
```

**Lưu ý:** File CSV phải được lưu với encoding UTF-8 BOM để không bị lỗi font.

## Yêu cầu

- WordPress 5.0+
- PHP 7.4+

## Changelog

### 1.0.0
- Phiên bản đầu tiên
- Flashcard với thiết kế 1 mặt đẹp mắt
- Audio player chuyên nghiệp
- Import/Export CSV
- Course system
- Progress tracking

## License

GPL v2 or later

## Author

[AhoKAIWA](https://github.com/datnong1210-arch)
