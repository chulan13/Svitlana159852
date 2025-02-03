Svitlana159852

# Użyte Algorytmy Szyfrowania

- W przedstawionym kodzie wykorzystano algorytm bcrypt do przechowywania haseł użytkowników. Konkretnie, funkcja ```password_hash()``` korzysta z algorytmu ```PASSWORD_BCRYPT```, który domyślnie stosuje algorytm Blowfish z losowym solowaniem.

- bcrypt charakteryzuje się:
    1. Wolnym haszowaniem, co czyni go odpornym na ataki brute-force.
    2. Automatycznym generowaniem soli, co uniemożliwia ataki słownikowe na powtarzające się hasła.
    3. Odpowiednie porównanie hasła następuje za pomocą ```password_verify()```, co zapewnia bezpieczne sprawdzenie haseł użytkownika.


# Możliwe Ataki

### 1. SQL Injection

> Opis ataku:
SQL Injection polega na wstrzyknięciu niebezpiecznego kodu SQL do zapytań do bazy danych, co może prowadzić do kradzieży danych, usunięcia tabel lub przejęcia kontroli nad aplikacją.
Miejsce podatne w kodzie:

```
$query = "SELECT posts.*, users.username FROM posts 
          JOIN users ON posts.user_id = users.id 
          WHERE posts.user_id = '$user_id' 
          AND posts.content LIKE '%$searchQuery%' 
          ORDER BY posts.created_at DESC";
```

Tutaj ```$user_id`` oraz ```$searchQuery``` są wstawiane bezpośrednio do zapytania SQL, co oznacza, że użytkownik może wstrzyknąć własny kod SQL.

> Jak naprawić?
Użycie przygotowanych zapytań (prepare i bindValue).
Unikanie interpolacji zmiennych w zapytaniach SQL.

### 2. Path Traversal

> Opis ataku:
Path Traversal pozwala atakującemu uzyskać dostęp do plików poza przewidzianym katalogiem poprzez manipulację ścieżką pliku (../).
Miejsce podatne w kodzie:

```
if (isset($_GET['view']) && file_exists($directory . '/' . $_GET['view'])) {
    $filePath = escapeshellarg($directory . '/' . $_GET['view']);
    exec("cat $filePath", $output);
}
```

Tutaj ```$_GET['view']``` nie jest sprawdzane pod kątem niebezpiecznych znaków (../), co umożliwia atakującemu odczytanie dowolnych plików systemowych.

> Jak naprawić?
Blokowanie znaków ../ w zmiennej ```$_GET['view'].```
Użycie realpath(), aby sprawdzić, czy ścieżka faktycznie wskazuje na plik w dozwolonym katalogu.