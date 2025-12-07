# Hemam Backend API

API Backend لموقع همم للتطوير والتدريب، مبني بـ Laravel 12 مع Clean Architecture.

## 🚀 Features

- ✅ RESTful APIs للمحتوى (برامج، مشاريع، مخيمات، مدربون)
- ✅ Form APIs مع Email Notifications
- ✅ Admin APIs كاملة مع Authentication
- ✅ Image Upload & Processing
- ✅ Caching (Redis-ready)
- ✅ Rate Limiting
- ✅ Multi-language (AR/EN)

## 📋 Requirements

- PHP 8.2+
- MySQL 8.0+
- Composer
- Laravel 12

## ⚙️ Installation

```bash
# 1. Clone the repository
git clone https://github.com/MaiOsamaALMoqayad/hemam-backend.git
cd hemam-backend

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Configure database in .env
DB_DATABASE=hemam
DB_USERNAME=root
DB_PASSWORD=

# 6. Run migrations
php artisan migrate

# 7. Seed database (optional)
php artisan db:seed

# 8. Create storage link
php artisan storage:link

# 9. Start server
php artisan serve
```

## 📧 Email Configuration

### Development (Mailtrap)
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### Production (Gmail)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
ADMIN_EMAIL=admin@hemam.com
```

## 📚 API Documentation

### Public APIs

#### Content APIs
- `GET /api/v1/annual-programs` - جميع البرامج السنوية
- `GET /api/v1/projects` - جميع المشاريع
- `GET /api/v1/camps/open` - المخيمات المفتوحة
- `GET /api/v1/camps/closed` - المخيمات المغلقة
- `GET /api/v1/camps/{id}` - تفاصيل مخيم
- `GET /api/v1/trainers` - جميع المدربين
- `GET /api/v1/statistics` - الإحصائيات
- `GET /api/v1/settings` - إعدادات الموقع
- `GET /api/v1/search?q=query` - البحث العام

#### Form APIs
- `POST /api/v1/contact` - فورم التواصل
- `POST /api/v1/trainer-applications` - فورم الانضمام كمدرب
- `POST /api/v1/consultations` - فورم استشارة خبير

### Admin APIs

#### Authentication
- `POST /api/admin/login` - تسجيل الدخول
- `POST /api/admin/logout` - تسجيل الخروج
- `GET /api/admin/user` - معلومات المستخدم

#### Resources (Protected)
All require `Authorization: Bearer {token}`

- **Dashboard:** `GET /api/admin/dashboard`
- **Annual Programs:** CRUD endpoints
- **Projects:** CRUD endpoints
- **Camps:** CRUD endpoints
- **Trainers:** CRUD endpoints
- **Contacts:** Management endpoints
- **Applications:** Management endpoints
- **Consultations:** Management endpoints
- **Statistics:** GET, PUT endpoints
- **Settings:** GET, PUT endpoints

## 🔐 Admin Credentials

Default admin users (from seeder):
```
Email: admin@hemam.com
Password: password123

Email: mai@hemam.com
Password: password123
```

## 🗂️ Project Structure

```
app/
├── Domain/              # Business Logic Layer
│   ├── AnnualPrograms/
│   ├── Projects/
│   ├── Camps/
│   └── ...
├── Http/
│   ├── Controllers/
│   │   ├── API/        # Public APIs
│   │   └── Admin/      # Admin APIs
│   ├── Requests/       # Form Validation
│   └── Resources/      # API Resources
├── Models/             # Eloquent Models
├── Notifications/      # Email Notifications
└── Helpers/            # Helper Classes

database/
├── migrations/         # Database Schema
└── seeders/           # Test Data

routes/
└── api.php            # API Routes
```

## 🧪 Testing

```bash
# Run tests
php artisan test

# Test specific feature
php artisan test --filter=ContactTest
```

## 🚀 Deployment

### Production Setup

```bash
# 1. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 2. Set production environment
APP_ENV=production
APP_DEBUG=false

# 3. Setup queue worker
php artisan queue:work --daemon

# 4. Setup cron job for scheduled tasks
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Performance

- **Caching:** Redis recommended for production
- **Queue:** Use Redis/Database for emails
- **Images:** Automatically optimized with Intervention Image

## 🛡️ Security

- Rate Limiting on all public endpoints
- Sanctum Authentication for Admin APIs
- CORS configured for frontend
- Input validation on all forms
- XSS protection with strip_tags

## 👥 Team

- **Backend Developer:** Mai Osama AL Moqayad
- **Frontend Developer:** Osama Alghoul

## 📄 License

Private Project - All Rights Reserved

## 🤝 Contributing

This is a private project. For any issues, contact the development team.
