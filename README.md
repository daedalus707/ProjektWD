#  Projekt Giełdy Towarowej (PHP + MySQL + Docker)

Ten projekt symuluje rynek towarowy dla 4 graczy (firm) i administratora, z możliwością składania ofert, rozliczania rynku oraz wizualizacją wyników sprzedaży.

##  Uruchomienie projektu w Dockerze

1. Upewnij się, że masz zainstalowane:
   - Docker
   - Docker Compose

2. W katalogu projektu uruchom polecenie:

   ```bash
   docker compose up --build
   ```

3. Aplikacja będzie dostępna pod adresem: `http://localhost:8080`

##  Uruchomienie gry

Aby zainicjować grę i bazę danych (utworzyć użytkowników i wyczyścić oferty):

Otwórz w przeglądarce: `http://localhost:8080/start.php`

##  Domyślne dane logowania

| Rola            | Login | Hasło |
|------------------|--------|--------|
| Gracz A        | A      | A      |
| Gracz B        | B      | B      |
| Gracz C        | C      | C      |
| Gracz D        | D      | D      |
| Administrator X | X      | X      |

###  Zmiana danych logowania

Login i hasło można zmienić w pliku `start.php` w sekcji `INSERT INTO users (...)`.

##  Logowanie

Strona logowania: `http://localhost:8080/login.html`

- Gracze są przekierowani do formularza ofert (`user.php`)
- Administrator do panelu (`admin.php`)

##  Funkcja rynku

Funkcja popytu znajduje się w pliku `utils.php`:

```php
function demand($price) {
    $A = 10;
    $B = 4000;
    if ($price - $A == 0) return 0;
    return floor($B / ($price - $A));
}
```

Można ją modyfikować zgodnie z potrzebami.

##  Ograniczenia

Ze względu na właściwości funkcji popytu oraz sposób generowania wykresów:
- Zalecane są ceny i ilości powyżej 20 (dla lepszej wizualizacji)
- Dla bardzo małych wartości wykres może być mało czytelny lub pusty

##  Autorzy Projektu

- Jakub Kreft 18237
- Tomasz Grzegorek 76982
- Bartosz Guz
