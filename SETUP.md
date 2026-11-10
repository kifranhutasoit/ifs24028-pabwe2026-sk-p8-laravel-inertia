# Setup BlogVerse Pro

Salin isi folder ini ke root proyek (timpa file yang sama), lalu jalankan:

```powershell
# 1. Hapus file bawaan yang tidak dipakai
Remove-Item resources\views\welcome.blade.php -ErrorAction SilentlyContinue
Remove-Item resources\js\app.js, resources\js\bootstrap.js -ErrorAction SilentlyContinue

# 2. Tambahkan ke .env
#    OPENAI_API_KEY=sk-...
#    OPENAI_MODEL=gpt-4o-mini
#    DB_CONNECTION=pgsql  DB_DATABASE=pabwe_p8_blog  (isi host/user/password)

# 3. Database + storage
php artisan migrate:fresh --seed
php artisan storage:link

# 4. Jalankan (dua terminal)
npm run dev
php artisan serve
```

Login contoh: `admin` / `admin` (admin), `member` / `member` (penulis).

## Test

```powershell
php artisan test
# coverage butuh Xdebug atau PCOV aktif:
php artisan test --coverage --min=100
composer audit
```
