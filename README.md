<!-- # 🚀 Quy ước Git Workflow cho Dự án

---

## 1. 🏷️ Các nhánh chính
- **`main`** → 🌐 Code chạy **production** (⚠️ chỉ merge khi release).
- **`develop`** → 🧪 Code tích hợp, test chung.
- **`feature/...`** → ✨ Nhánh cho từng chức năng.
- **`hotfix/...`** → 🛠️ Sửa lỗi gấp trên production.
- **`release/...`** → 📦 Chuẩn bị bản phát hành.

---

## 2. 👩‍💻 Cách làm việc của mỗi thành viên

> ⚠️ **LƯU Ý QUAN TRỌNG**  
> - Nếu **mới tham gia dự án lần đầu**:  
>   Sau khi clone repo, phải chạy lệnh sau để đồng bộ nhánh từ remote:
>   ```bash
>   git fetch origin
>   ```
> - ❌ **KHÔNG** được code hoặc push trực tiếp vào `main` hoặc `develop`.

---

### 🔄 Luôn cập nhật nhánh `develop` trước khi code
```bash
git checkout develop
git pull origin develop -->
