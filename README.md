PetStore
===============================

# Vývoj
Pre vývoj stačí v **root folder** spustiť command `docker compose up -d --build`

Následne je na adrese `127.0.0.1:80` web server prístupný (Všetky zmeny v aplikácie sa bez resetu containeru prejavia hneď)

# Integration tests

### Ubuntu
Spustíme script `run-integration-tests.sh` ktorý spustí web server, vykoná testy a uprace po sebe.

**Tento script je spustitelný aj v pipeline priamo**

### Windows
Spustíme script `run-integration-tests.ps1` ktorý spustí web server, vykoná testy a uprace po sebe.


# Disclaimer
- Nie všetky endponty sú podla swagger dokumentácie, z dôvodu že nedávali zmysel.
  - Niektoré ktoré som aj podla nej spravil sú mi strašne odporné, napr. vracať 405 response code pre nevalidný vstup
  - Dalej uplne nezdokumentovane chybné stavy ktoré realne môžu nastať
    - Napr. endpoint pre partial update nevyhadzuje 404 NotFound iba 405 pre invalid vstup
    - Alebo upload image nemá žiadne chybové stavy? Čo ak neexistuje Pet? Keby to aj očakáva ten response rovnaky ako v 200, chybaju tam expected response codes pre dané chyby
- Neviem či bolo súčasťou aj authentication, ale v dokumentácií je tiež trošku všelijak a po skontrolovaní java repozitáre ktorá implementuje túto api, tiež nemajú authentication
  - Ak by ale bolo treba, pravdepodone by som vygeneroval JWT token s client_id a scopes, a nasledne ich kontroloval v jednotlivých endpoints
- Nahravanie obrázkov má check či je Image priamo z FileUpload. Bohu žial sa mi nepodarilo nastaviť GD extension, ktorý zastrešuje tento check aby podporoval jpeg formát
- Web server funguje iba na porte 80 z dôvodu aby som nemusel generovať a inštalovať certifikáty pre tak malý projekt, postačí dúfam aj http :)


# Reasoning
- **[ApiRouter]**
  - Default nette router nepodporuje mapovať na iné HTTP Methods ako GET, takže bolo treba router ktorý to podporuje
- **[Repository pattern]**
  - Jednoduchý prechod na napr. mysql databazu. Vytvori sa Mysql<Entity>Repository ktorý dedí z daného interface.
    - Sice mať nejakú IDatabase a jej implementácie k danej Databáze je mať fajn, no vźdy sú v kóde custom queries a nikdy to nie je drop-in replacement.
      Cez repository pattern sa drop-in replace jednoducho dá vykonať
- **[Result pattern]**
  - Je to skôr iba ako osobný experiment, tento pattern napr. v C# funguje krásne a nemusíme znásilňovať Exceptions, nehovoriac o tom že sa pekne fluentne dá spracovať result
    - Osobne nemám rád všade hádzať exception, Exception beriem ako vyložene chybu, Chyba stavu dat, ktorý očakávame  amôźe nastať by nemal byť Exception ale nejaký ErrorResult
- **[Integration tests]**
  - Testuje komplet API a nie je závislá na projekte aplikácie, testuje vstupy a výstupy, nie je treba mocking.
- **[SDK]**
  - Api endpointy beriem ako keby to je iná micro-service alebo iná služba na ktorú vidí naša Web aplikácia
  - Pre jednoduchšiu prácu s API som vytvoril SDK ktorá nám vytvára jednoduché vyuźitie danej api bez potreby poznať priamo vstupy / výstupy apiny.
    Zabaluje chybové stavy do vlastnej Exception, ktorej lahko rozumie consumer Api cez SDK.
  - Osobne si myslím že k apinám by autor mal dodavať SDk pre lahkú integráciu :)


# Rozšírenie Pet o ďalší stĺpec
- Do datového objektu `PetStore\Data\Pet` sa pridá nový atribút
  - Ak je atribút povinný (Nie nullable a nemá default hodnotu) je trbea vyplniť pre všetky existujúce zvieratá manuálne v xml súbore
    - **[Optional]** Da sa tomu obisť tak že sa dá ako nepovinný, následne sa cez UI / Api doplnia tieto data a nasledne sa dá ako povinný (Nebude treba manualna uprava XML)
- Do formulára pridáme nový input pre nový field v `PetStore\Presenters\Home\HomePresenter :: createComponentForm`
  - **[Optional]** Ak je treba simple validácia tak rozšíri sa aj `PetStore\Presenters\Home\HomePresenter :: validateForm`
  - **[Optional]** Ak je treba validovať cez inú entitu ako napr. aj Category alebo Tag, tak sa rozšíri v servisnej metóde (Tu sú zvlášť pre create a update)