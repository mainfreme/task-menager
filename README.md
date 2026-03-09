# Task Manager

Aplikacja do zarządzania zadaniami z API GraphQL, uwierzytelnianiem JWT oraz Event Sourcing (logowaniem zdarzeń: utworzenie, edycja, zmiana statusu, wyświetlenie zadania).

## Wymagania

Docker

## Uruchomienie - Docker 

```bash
# Uruchom kontenery (PHP, Nginx, PostgreSQL)
docker-compose up -d

# Zainstaluj zależności (w kontenerze PHP)
docker-compose exec php composer install

# Wygeneruj klucze JWT (jeśli nie istnieją)
docker-compose exec php php bin/console lexik:jwt:generate-keypair --skip-if-exists

# Uruchom migracje
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# Załaduj dane testowe (użytkownik admin)
docker-compose exec php php bin/console doctrine:fixtures:load --append
```

Aplikacja dostępna pod: **http://localhost:8080**


## Uzupełnienie bazy danych

### Migracje (schemat tabel)

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Fixtures (użytkownik testowy)

```bash
php bin/console doctrine:fixtures:load --append
```

Tworzy użytkownika:
- **Email:** admin@test.pl
- **Hasło:** admin
- **Rola:** ROLE_ADMIN (username = admin)

### Import użytkowników z API (JSONPlaceholder)

Zaloguj się i wywołaj mutację `syncUsersFromApi` – pobierze użytkowników z zewnętrznego API.

---

## Endpoint GraphQL

Wszystkie zapytania i mutacje są obsługiwane przez jeden endpoint:

| Metoda | URL |
|--------|-----|
| POST | `/graphql` |

**Nagłówek autoryzacji** (dla zapytań wymagających logowania):
```
Authorization: Bearer <token>
```

---

## Mutacje (Mutations)

### `login` – logowanie (bez tokena)

**Wymagane dane:**
```json
{
  "input": {
    "email": "String!",
    "password": "String!"
  }
}
```

**Przykład:**
```graphql
mutation {
  login(input: { email: "admin@test.pl", password: "admin" }) {
    token
    refreshToken
    expiresAt
  }
}
```

---

### `refreshToken` – odświeżenie tokena (bez tokena)

**Wymagane dane:**
```json
{
  "input": {
    "refreshToken": "String!"
  }
}
```

---

### `syncUsersFromApi` – import użytkowników z API (wymaga tokena)

Pobiera użytkowników z zewnętrznego API [JSONPlaceholder](https://jsonplaceholder.typicode.com/users) i zapisuje ich w lokalnej bazie. Użytkownicy z istniejącym adresem email są pomijani. Wszyscy zaimportowani użytkownicy otrzymują domyślne hasło `secret123`.

**Wymagania:** nagłówek `Authorization: Bearer <token>`

**Argumenty:** brak

**Zwracany typ:** `ImportUsersResult`

| Pole | Typ | Opis |
|------|-----|------|
| imported | Int! | Liczba zaimportowanych użytkowników |
| skipped | Int! | Liczba pominiętych (już istnieją w bazie) |
| failed | Int! | Liczba nieudanych importów |
| errors | [String!]! | Lista komunikatów błędów |

**Przykład:**
```graphql
mutation {
  syncUsersFromApi {
    imported
    skipped
    failed
    errors
  }
}
```

---

### `createTask` – utworzenie zadania (wymaga tokena)

**Wymagane dane:**
```json
{
  "input": {
    "name": "String!",
    "description": "String",
    "assignedUserId": "Int!"
  }
}
```

| Pole | Typ | Wymagane |
|------|-----|----------|
| name | String | tak |
| description | String | nie |
| assignedUserId | Int | tak |

---

### `updateTask` – edycja zadania (wymaga tokena)

**Wymagane dane:**
```json
{
  "id": "Int!",
  "input": {
    "name": "String!",
    "description": "String",
    "assignedUserId": "Int"
  }
}
```

| Pole | Typ | Wymagane |
|------|-----|----------|
| id | Int | tak |
| name | String | tak |
| description | String | nie |
| assignedUserId | Int | nie |

---

### `changeTaskStatus` – zmiana statusu (wymaga tokena)

**Wymagane dane:**
```json
{
  "id": "Int!",
  "status": "TaskStatus!"
}
```

**TaskStatus:** `TODO` | `IN_PROGRESS` | `DONE`

---

### `deleteTask` – usunięcie zadania (wymaga tokena)

**Wymagane dane:**
```json
{
  "id": "Int!"
}
```

---

## Zapytania (Queries)

Wszystkie zapytania poniżej wymagają tokena JWT w nagłówku `Authorization`.

### `task` – pojedyncze zadanie

**Argumenty:** `id: Int!`

### `tasks` – lista zadań

- Admin: wszystkie zadania
- Użytkownik: tylko przypisane do niego

### `tasksByUser` – zadania użytkownika

**Argumenty:** `userId: Int!`

### `me` – zalogowany użytkownik

### `users` – lista użytkowników

---

## Przykładowe zapytania

### Logowanie
```graphql
mutation {
  login(input: { email: "admin@test.pl", password: "admin" }) {
    token
    refreshToken
    expiresAt
  }
}
```

### Utworzenie zadania (z tokenem)
```graphql
mutation {
  createTask(input: {
    name: "Nowe zadanie"
    description: "Opis"
    assignedUserId: 1
  }) {
    id
    name
    status
  }
}
```

### Pobranie zadania z historią eventów
```graphql
query {
  task(id: 1) {
    id
    name
    status
    events {
      id
      eventType
      payload
      userId
      occurredAt
    }
  }
}
```

---

## Event Sourcing

Aplikacja loguje zdarzenia związane z zadaniami (przez Symfony Messenger – synchronicznie):

| Typ eventu | Opis |
|------------|------|
| task.created | Utworzenie zadania |
| task.updated | Edycja zadania |
| task.status_changed | Zmiana statusu |
| task.viewed | Wyświetlenie zadania przez użytkownika |

Każdy event zapisuje pełny payload (JSON) oraz `userId` (kto wykonał akcję). Lista eventów jest dostępna w polu `events` przy zapytaniu o zadanie.
