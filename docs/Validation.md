# Validation Class

Form verilerini doğrular. Zengin kural seti, özel mesajlar ve kolay kullanım sunar.

## Temel Kullanım

```php
$validation = new Validation($_POST);

$validation->rule('email', 'E-posta', 'required|email')
          ->rule('password', 'Şifre', 'required|min:6');

if ($validation->validate()) {
    // Doğrulama başarılı
    $validData = $validation->getValidData();
} else {
    // Hatalar var
    $errors = $validation->getErrors();
}
```

## Factory Metodu

### make($data = [])
Validation nesnesi oluşturur.

```php
$validation = Validation::make($_POST);
```

## Kural Tanımlama

### rule($field, $label, $rules)
Doğrulama kuralı ekler.

```php
$validation->rule('username', 'Kullanıcı Adı', 'required|min:3|max:20');
$validation->rule('email', 'E-posta', 'required|email');
$validation->rule('age', 'Yaş', 'required|integer|min:18');
```

## Doğrulama Kuralları

### required
Alan gereklidir.

```php
$validation->rule('name', 'Ad', 'required');
```

### email
Geçerli e-posta adresi olmalıdır.

```php
$validation->rule('email', 'E-posta', 'required|email');
```

### min:param
Minimum karakter uzunluğu.

```php
$validation->rule('password', 'Şifre', 'required|min:6');
$validation->rule('username', 'Kullanıcı Adı', 'min:3');
```

### max:param
Maksimum karakter uzunluğu.

```php
$validation->rule('title', 'Başlık', 'max:100');
$validation->rule('description', 'Açıklama', 'max:500');
```

### numeric
Sayısal değer olmalıdır.

```php
$validation->rule('price', 'Fiyat', 'numeric');
$validation->rule('quantity', 'Miktar', 'required|numeric');
```

### alpha
Sadece harflerden oluşmalıdır.

```php
$validation->rule('first_name', 'Ad', 'alpha');
```

### alphanumeric
Sadece harf ve rakamlardan oluşmalıdır.

```php
$validation->rule('username', 'Kullanıcı Adı', 'alphanumeric');
```

### url
Geçerli URL olmalıdır.

```php
$validation->rule('website', 'Web Sitesi', 'url');
```

### date:format
Geçerli tarih olmalıdır.

```php
$validation->rule('birth_date', 'Doğum Tarihi', 'date:Y-m-d');
$validation->rule('created_at', 'Oluşturma Tarihi', 'date:d/m/Y');
```

### matches:field
Başka bir alanla eşleşmelidir.

```php
$validation->rule('password_confirm', 'Şifre Tekrar', 'matches:password');
```

### in:values
Belirtilen değerlerden biri olmalıdır.

```php
$validation->rule('status', 'Durum', 'in:active,inactive,pending');
$validation->rule('gender', 'Cinsiyet', 'in:male,female,other');
```

### regex:pattern
Regex desenine uymalıdır.

```php
$validation->rule('phone', 'Telefon', 'regex:/^[0-9]{10,11}$/');
$validation->rule('postal_code', 'Posta Kodu', 'regex:/^[0-9]{5}$/');
```

### integer
Tam sayı olmalıdır.

```php
$validation->rule('age', 'Yaş', 'integer');
$validation->rule('count', 'Sayı', 'required|integer');
```

### float
Ondalıklı sayı olmalıdır.

```php
$validation->rule('price', 'Fiyat', 'float');
$validation->rule('rating', 'Puan', 'float');
```

### boolean
Boolean değer olmalıdır.

```php
$validation->rule('is_active', 'Aktif', 'boolean');
$validation->rule('newsletter', 'Bülten', 'boolean');
```

### ip
Geçerli IP adresi olmalıdır.

```php
$validation->rule('ip_address', 'IP Adresi', 'ip');
```

### creditcard
Geçerli kredi kartı numarası olmalıdır.

```php
$validation->rule('card_number', 'Kart Numarası', 'creditcard');
```

## Doğrulama İşlemi

### validate($data = null)
Doğrulama yapar.

```php
if ($validation->validate()) {
    // Başarılı
} else {
    // Hatalı
}

// Farklı veri ile doğrula
if ($validation->validate($_GET)) {
    // GET verileri doğrulandı
}
```

### passes()
Doğrulama başarılı mı?

```php
if ($validation->passes()) {
    // Başarılı
}
```

### fails()
Doğrulama başarısız mı?

```php
if ($validation->fails()) {
    // Başarısız
}
```

## Hata İşleme

### getErrors()
Tüm hataları döndürür.

```php
$errors = $validation->getErrors();
foreach ($errors as $field => $message) {
    echo "$field: $message<br>";
}
```

### getError($field)
Belirli alanın hatasını döndürür.

```php
$emailError = $validation->getError('email');
if ($emailError) {
    echo "E-posta hatası: $emailError";
}
```

### getFirstError()
İlk hatayı döndürür.

```php
$firstError = $validation->getFirstError();
echo $firstError;
```

## Özel Mesajlar

### setMessage($rule, $message)
Özel hata mesajı ayarlar.

```php
$validation->setMessage('required', ':field alanı zorunludur!')
          ->setMessage('email', 'Geçerli bir :field adresi girin!')
          ->setMessage('min', ':field en az :param karakter olmalı!');
```

### Alan Bazlı Mesajlar
```php
$validation->setMessage('email.required', 'E-posta adresi gereklidir!')
          ->setMessage('email.email', 'Geçerli bir e-posta adresi girin!')
          ->setMessage('password.min', 'Şifre en az 6 karakter olmalıdır!');
```

## Doğrulanmış Veriler

### getValidData()
Doğrulanmış verileri döndürür.

```php
if ($validation->passes()) {
    $validData = $validation->getValidData();
    // Sadece doğrulanmış alanlar
}
```

## Örnek Kullanımlar

### Kullanıcı Kaydı
```php
class RegisterController extends Controller
{
    public function store()
    {
        $validation = Validation::make($this->request->post());
        
        $validation->rule('first_name', 'Ad', 'required|alpha|min:2|max:50')
                  ->rule('last_name', 'Soyad', 'required|alpha|min:2|max:50')
                  ->rule('email', 'E-posta', 'required|email')
                  ->rule('username', 'Kullanıcı Adı', 'required|alphanumeric|min:3|max:20')
                  ->rule('password', 'Şifre', 'required|min:8')
                  ->rule('password_confirm', 'Şifre Tekrar', 'required|matches:password')
                  ->rule('age', 'Yaş', 'required|integer|min:18')
                  ->rule('terms', 'Şartlar', 'required|boolean');
        
        // Özel mesajlar
        $validation->setMessage('email.email', 'Geçerli bir e-posta adresi girin!')
                  ->setMessage('password.min', 'Şifre en az 8 karakter olmalıdır!')
                  ->setMessage('age.min', 'En az 18 yaşında olmalısınız!')
                  ->setMessage('terms.required', 'Kullanım şartlarını kabul etmelisiniz!');
        
        if ($validation->fails()) {
            return $this->response->validationError($validation->getErrors());
        }
        
        $userData = $validation->getValidData();
        unset($userData['password_confirm'], $userData['terms']);
        
        $user = User::create($userData);
        
        return $this->response->created($user);
    }
}
```

### Profil Güncelleme
```php
class ProfileController extends Controller
{
    public function update()
    {
        $validation = Validation::make($this->request->post());
        
        $validation->rule('first_name', 'Ad', 'required|alpha|min:2|max:50')
                  ->rule('last_name', 'Soyad', 'required|alpha|min:2|max:50')
                  ->rule('email', 'E-posta', 'required|email')
                  ->rule('phone', 'Telefon', 'regex:/^[0-9]{10,11}$/')
                  ->rule('website', 'Web Sitesi', 'url')
                  ->rule('bio', 'Biyografi', 'max:500')
                  ->rule('birth_date', 'Doğum Tarihi', 'date:Y-m-d')
                  ->rule('gender', 'Cinsiyet', 'in:male,female,other');
        
        if ($validation->fails()) {
            Session::flash('error', $validation->getFirstError());
            return $this->redirect('/profile/edit');
        }
        
        $validData = $validation->getValidData();
        $user = User::find(Session::get('user_id'));
        $user->update($validData);
        
        Session::flash('success', 'Profil güncellendi!');
        return $this->redirect('/profile');
    }
}
```

### API Endpoint
```php
class ApiController extends Controller
{
    public function createPost()
    {
        $validation = Validation::make($this->request->input());
        
        $validation->rule('title', 'Başlık', 'required|min:5|max:200')
                  ->rule('content', 'İçerik', 'required|min:10')
                  ->rule('category_id', 'Kategori', 'required|integer')
                  ->rule('tags', 'Etiketler', 'alphanumeric')
                  ->rule('status', 'Durum', 'in:draft,published,archived')
                  ->rule('publish_date', 'Yayın Tarihi', 'date:Y-m-d H:i:s');
        
        if ($validation->fails()) {
            return $this->response->validationError($validation->getErrors());
        }
        
        $postData = $validation->getValidData();
        $postData['author_id'] = $this->getCurrentUserId();
        
        $post = Post::create($postData);
        
        return $this->response->created($post);
    }
}
```

### Çok Adımlı Form
```php
class WizardController extends Controller
{
    public function step1()
    {
        if ($this->request->isPost()) {
            $validation = Validation::make($this->request->post());
            
            $validation->rule('company_name', 'Şirket Adı', 'required|min:2|max:100')
                      ->rule('industry', 'Sektör', 'required|in:tech,finance,health,education,other')
                      ->rule('employee_count', 'Çalışan Sayısı', 'required|integer|min:1');
            
            if ($validation->fails()) {
                $this->set('errors', $validation->getErrors());
                $this->set('old_input', $this->request->post());
            } else {
                Session::set('wizard_step1', $validation->getValidData());
                return $this->redirect('/wizard/step2');
            }
        }
        
        $this->render('wizard/step1');
    }
}
```

### Dosya Yükleme Doğrulama
```php
class UploadController extends Controller
{
    public function avatar()
    {
        $validation = Validation::make($this->request->post());
        
        // Temel alanlar
        $validation->rule('user_id', 'Kullanıcı', 'required|integer');
        
        // Dosya kontrolü
        if ($this->request->hasFile('avatar')) {
            $file = $this->request->file('avatar');
            
            // Dosya boyutu (2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                $validation->addError('avatar', 'Dosya boyutu 2MB\'dan büyük olamaz.');
            }
            
            // Dosya türü
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                $validation->addError('avatar', 'Sadece JPEG, PNG ve GIF dosyaları kabul edilir.');
            }
        } else {
            $validation->addError('avatar', 'Avatar dosyası gereklidir.');
        }
        
        if ($validation->fails()) {
            return $this->response->validationError($validation->getErrors());
        }
        
        // Dosya yükleme işlemi...
    }
}
```

## İpuçları

1. **Zincirleme**: Kuralları zincirleme şekilde tanımlayın
2. **Özel Mesajlar**: Kullanıcı dostu mesajlar yazın
3. **Alan Adları**: Türkçe etiketler kullanın
4. **Güvenlik**: Tüm kullanıcı girdilerini doğrulayın
5. **Performance**: Gereksiz kurallar eklemeyin