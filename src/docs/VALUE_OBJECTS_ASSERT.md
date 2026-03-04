# Value Objects - Dokumentacja

## 📦 Biblioteka: webmozart/assert

Wszystkie Value Objects używają biblioteki `webmozart/assert` do walidacji.

### Instalacja

```bash
composer require webmozart/assert
```

---

## 🎯 Dostępne metody Webmozart Assert

### Podstawowe asercje:

```php
use Webmozart\Assert\Assert;

// Sprawdzanie pustych wartości
Assert::notEmpty($value, 'Wartość nie może być pusta');

// Walidacja email
Assert::email($email, 'Email nie jest prawidłowy');

// Długość stringa
Assert::length($value, 10, 'Wartość musi mieć dokładnie %2$d znaków');
Assert::minLength($value, 3, 'Wartość musi mieć co najmniej %2$d znaków');
Assert::maxLength($value, 100, 'Wartość może mieć maksymalnie %2$d znaków');

// Zakresy numeryczne
Assert::range($value, 0, 100, 'Wartość musi być między %2$s a %3$s');
Assert::greaterThan($value, 0, 'Wartość musi być większa niż %2$s');
Assert::lessThan($value, 100, 'Wartość musi być mniejsza niż %2$s');

// Typy
Assert::string($value, 'Wartość musi być stringiem');
Assert::integer($value, 'Wartość musi być liczbą całkowitą');
Assert::numeric($value, 'Wartość musi być liczbą');
Assert::boolean($value, 'Wartość musi być wartością logiczną');

// Regex
Assert::regex($value, '/^[a-z]+$/', 'Wartość musi zawierać tylko małe litery');

// URL
Assert::url($url, 'URL nie jest prawidłowy');

// UUID
Assert::uuid($uuid, 'UUID nie jest prawidłowy');
```

---

## 📋 Przykłady użycia w projekcie

### Email Value Object

```php
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Email extends StringValueObject
{
    protected function validate(string $value): void
    {
        Assert::notEmpty($value, 'Email nie może być pusty');
        Assert::email($value, 'Email nie jest prawidłowy');
    }
}

// Użycie:
$email = Email::fromString('user@example.com');
echo $email->getValue(); // user@example.com
```

### Username Value Object

```php
#[ORM\Embeddable]
final class Username extends StringValueObject
{
    protected function validate(string $value): void
    {
        Assert::notEmpty($value, 'Nazwa użytkownika nie może być pusta');
        Assert::minLength($value, 3, 'Nazwa użytkownika musi mieć co najmniej %2$d znaki');
    }
}

// Użycie:
$username = Username::fromString('john_doe');
```

### Geo Value Object (z zakresem)

```php
final class Geo extends ValueObject
{
    public function __construct(string $lat, string $lng)
    {
        Assert::notEmpty($lat, 'Szerokość geograficzna nie może być pusta');
        Assert::notEmpty($lng, 'Długość geograficzna nie może być pusta');
        Assert::range(
            (float)$lat, 
            -90, 
            90, 
            'Szerokość geograficzna musi być między %2$s a %3$s'
        );
        Assert::range(
            (float)$lng, 
            -180, 
            180, 
            'Długość geograficzna musi być między %2$s a %3$s'
        );

        $this->lat = $lat;
        $this->lng = $lng;
    }
}

// Użycie:
$geo = Geo::fromString('52.237049', '21.017532');
```

---

## 🔑 Placeholdery w komunikatach błędów

Webmozart Assert używa placeholderów:

- `%s` - wartość przekazana do asercji (pierwszy argument)
- `%2$s`, `%3$s` - kolejne argumenty metody Assert

### Przykłady:

```php
// %s - wartość, %2$d - minLength (3)
Assert::minLength($value, 3, 'Wartość "%s" musi mieć co najmniej %2$d znaki');
// Błąd: Wartość "ab" musi mieć co najmniej 3 znaki

// %s - wartość, %2$s - min (-90), %3$s - max (90)
Assert::range($lat, -90, 90, 'Lat "%s" musi być między %2$s a %3$s');
// Błąd: Lat "95" musi być między -90 a 90
```

---

## ✅ Zalety webmozart/assert

1. **Czytelność** - kod jest bardziej deklaratywny
2. **Spójność** - wszystkie błędy mają ten sam format
3. **Łatwość w utrzymaniu** - jedna biblioteka, jeden styl
4. **Standardowa biblioteka** - używana w wielu projektach DDD
5. **Statyczna analiza** - wsparcie dla PHPStan/Psalm
6. **Bogate API** - 100+ asercji out-of-the-box

---

## 📖 Więcej informacji

Oficjalna dokumentacja: https://github.com/webmozart/assert

### Wszystkie dostępne asercje:

- String assertions: `string()`, `stringNotEmpty()`, `alpha()`, `digits()`, `alnum()`, `lower()`, `upper()`, `startsWith()`, `endsWith()`, `contains()`, `regex()`, `unicodeLetters()`
- Number assertions: `integer()`, `integerish()`, `float()`, `numeric()`, `natural()`, `range()`, `greaterThan()`, `greaterThanEq()`, `lessThan()`, `lessThanEq()`
- Boolean assertions: `boolean()`, `true()`, `false()`
- Array assertions: `isArray()`, `isTraversable()`, `keyExists()`, `keyNotExists()`, `count()`, `minCount()`, `maxCount()`
- Object assertions: `object()`, `isInstanceOf()`, `notInstanceOf()`, `propertyExists()`, `methodExists()`
- File assertions: `file()`, `fileExists()`, `directory()`, `readable()`, `writable()`
- Special assertions: `null()`, `notNull()`, `isEmpty()`, `notEmpty()`, `email()`, `url()`, `uuid()`, `ip()`, `ipv4()`, `ipv6()`

---

## 🎯 Best Practices

### 1. Zawsze podawaj komunikaty błędów

```php
// ✅ Dobrze
Assert::notEmpty($value, 'Email nie może być pusty');

// ❌ Źle (komunikat domyślny)
Assert::notEmpty($value);
```

### 2. Używaj placeholderów dla dynamicznych wartości

```php
// ✅ Dobrze
Assert::range($lat, -90, 90, 'Lat "%s" musi być między %2$s a %3$s');

// ❌ Źle (hardcoded values)
Assert::range($lat, -90, 90, 'Lat musi być między -90 a 90');
```

### 3. Grupuj powiązane asercje

```php
public function __construct(string $value)
{
    Assert::notEmpty($value, 'Wartość nie może być pusta');
    Assert::minLength($value, 3, 'Wartość musi mieć co najmniej %2$d znaki');
    Assert::maxLength($value, 100, 'Wartość może mieć maksymalnie %2$d znaków');
    Assert::regex($value, '/^[a-zA-Z0-9_]+$/', 'Wartość może zawierać tylko litery, cyfry i podkreślnik');
    
    $this->value = $value;
}
```

---

## 🧪 Testowanie Value Objects

```php
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = Email::fromString('user@example.com');
        $this->assertEquals('user@example.com', $email->getValue());
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email nie jest prawidłowy');
        
        Email::fromString('invalid-email');
    }

    public function testEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email nie może być pusty');
        
        Email::fromString('');
    }
}
```
