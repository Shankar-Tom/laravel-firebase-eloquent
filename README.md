# shankar/laravel-firebase-db

Lightweight Eloquent-like models for **Firebase Realtime Database** with **CRUD, where, whereIn, orderBy, limit, paginate, timestamps**.  
Supports **Laravel 10/11/12**.

## Install (local path)

1) 

```bash
composer require shankar/laravel-firebase-db:@dev
php artisan vendor:publish --tag=firebase-db-config
```

2) Configure `.env`:

```
FIREBASE_CREDENTIALS=/full/path/to/storage/app/firebase/credentials.json
FIREBASE_DATABASE_URL=https://your-project-id-default-rtdb.firebaseio.com
FIREBASE_DB_PER_PAGE=15
```

3) Create a model:

```php
use Shankar\FirebaseDb\Model;

class User extends Model
{
    protected string $collection = 'users';
    protected array $fillable = ['name','email','age'];
}
```

## Usage

```php
// Create
$user = User::create(['name'=>'Shankar','email'=>'a@b.com','age'=>27]);

// Find
$u = User::find($user->id);

// Update
$u->update(['age'=>28]);

// Delete
$u->delete();

// Query
$adults = User::where('age','>=',18)->orderBy('name')->limit(20)->get();

// whereIn + paginate
$list = User::whereIn('status',['active','trial'])->orderBy('created_at','desc')->paginate(10);
```

> Pagination returns a normal `LengthAwarePaginator`, so in Blade just do: `{{ $list->links() }}`
