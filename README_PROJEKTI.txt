# Monitorimi i Komunikimit të Fëmijëve

Ky projekt është një aplikacion web në PHP dhe MySQL që ndihmon në monitorimin e komunikimit të fëmijëve. Aplikacioni ka dy role kryesore: prind dhe fëmijë.

## Funksionalitetet kryesore

- Regjistrim dhe hyrje për prind dhe fëmijë
- Verifikim emaili me kod gjatë regjistrimit
- Profili i përdoruesit
- Lista e shokëve për fëmijët
- Komunikim me mesazhe mes fëmijëve
- Analizim i mesazheve për fjalë të papërshtatshme
- Krijim alarmesh për prindin kur zbulohet komunikim i papërshtatshëm
- Njoftime për mesazhe dhe alarme
- Ngarkim fotoje profili

## Rolet

### Prindi
Prindi mund të:
- shohë dashboard-in e tij
- shohë alarmet e krijuara nga mesazhet e papërshtatshme
- shohë njoftimet
- menaxhojë profilin e tij

### Fëmija
Fëmija mund të:
- krijojë profil dhe të lidhet me ID-në e prindit
- shtojë shokë
- komunikojë me shokët e pranuar
- marrë njoftime
- ndryshojë të dhënat e profilit sipas validimeve të sistemit

## Siguria dhe validimi

Aplikacioni përdor validim server-side në PHP. Kjo do të thotë që edhe nëse përdoruesi ndryshon të dhënat nga Inspect në browser, ndryshimet nuk pranohen nëse nuk kalojnë kontrollin në server.
