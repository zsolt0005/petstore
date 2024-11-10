PetStore
===============================

# Disclaimer
- Nie všetky endponty sú podla swagger dokumentácie, z dôvodu že nedávali zmysel.
  - Niektoré ktoré som aj podla nej spravil sú mi strašne odporné, napr. vracať 405 response code pre nevalidný vstup
  - Dalej uplne nezdokumentovane chybné stavy ktoré realne môžu nastať
    - Napr. endpoint pre partial update nevyhadzuje 404 NotFound iba 405 pre invalid vstup
- Neviem či bolo súčasťou aj authentication, ale v dokumentácií je tiež trošku všelijak a po skontrolovaní java repozitáre ktorá implementuje túto api, tiež nemajú authentication
  - Ak by ale bolo treba, pravdepodone by som vygeneroval JWT token s client_id a scopes, a nasledne ich kontroloval v jednotlivých endpoints
- Nahravanie obrázkov má check či je Image priamo z FileUpload. Bohu žial sa mi nepodarilo nastaviť GD extension, ktorý zastrešuje tento check aby podporoval jpeg formát
- Web server funguje iba na porte 80 z dôvodu aby som nemusel generovať a inštalovať certifikáty pre tak malý projekt, postačí dúfam aj http :)

# Vývoj
Pre vývojs tačí v **root folder** spustiť command `docker compose up -d --build`

Následne je na adrese `127.0.0.1:80` web server prístupný

# Integration tests

### Ubuntu
Spustíme script `run-integration-tests.sh` ktorý spustí web server, vykoná testy a uprace po sebe.

**Tento script je spustitelný aj v pipeline priamo**

### Windows
Spustíme script `run-integration-tests.ps1` ktorý spustí web server, vykoná testy a uprace po sebe.

# Rozšírenie Pet o ďalší stĺpec
TODO