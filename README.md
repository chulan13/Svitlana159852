Svitlana159852
### Krótki opis aplikacji

Niewielka aplikacja webowa do przechowania własnych notatek (dalej: postów) **bez dostępu do postów innych użytkowników**.

1. Dostępny jest widok rejestracji konta oraz logowania.
2. Po zalogowaniu użytkownik zostaje przekierowany na panel główny, na którym może tworzyć oraz przegłądać własne posty.
3. Na tym widoku dostępna jest również opcja wyszukania konkretnego posta.
4. W nawigacji są opcje do przekierowania się na widok przegłądania plików. Na tym widoku dostępne są opcje wyszukania pliku oraz przejrzenia jego zawartości.
5. W nawigacji znajduje się opcja wylogowania się.

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

**Przykład ataku:**
Logujemy się jako różni użytkownicy i wrzucamy posty z każdego konta, by się upewnić, że posty innego użytkownika są niedostępne.

<img width="452" alt="image" src="https://github.com/user-attachments/assets/bc6d870f-776b-41ea-910c-a2d8329dd99e" />


Przy zwykłym wyszukiwaniu też nie mamy do nich dostępu.

<img width="452" alt="image" src="https://github.com/user-attachments/assets/b60f247e-00ff-4798-bbfa-09787a50e48e" />


Ale jeśli sprobujemy użyć najbardziej standardowy przykład SQL Injection, to w tym przypadku z poziomu użytkownika 2 widzimy też posty innych użytkowników, np. 1.

<img width="452" alt="image" src="https://github.com/user-attachments/assets/53ac2b03-c47a-427d-8166-5e98bc748180" />



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

**Przykład ataku:**
Widok wyszukiwania i przegłądania plików. Klikamy na dowolny plik który chcemy zobaczyć.

<img width="452" alt="image" src="https://github.com/user-attachments/assets/ea465969-cf71-44f8-975c-e64fb2d36545" />


Pliki przedstawione na widoku są plikami przeznaczonymi dla aplikacji, ale co jeśli da się dostać do plików na samym serwerze?
W komponencie linku zamiast nazwy pliku wpisujemy ścieżkę ```../../../../../etc/passwd```
<img width="452" alt="image" src="https://github.com/user-attachments/assets/12d2f377-59d9-4383-a93a-b1304638a6e5" />
W tym przypadku dostaliśmy się do pliku */etc/passwd* na serwerze aplikacji.

> Jak naprawić?
Blokowanie znaków ../ w zmiennej ```$_GET['view'].```
Użycie realpath(), aby sprawdzić, czy ścieżka faktycznie wskazuje na plik w dozwolonym katalogu.
