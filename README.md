# GamesConsoles

## Requirements
Symfony: latest
php: 8.3+
twig: latest
db: mariadb

## project info
This is a project to store data on which games and consoles I own from different brands.
Also a random picker for when I don't know what to play.
Multiple users can each keep their own collection.

## data structure
User
- email
- password

Brand
- owner (user)
- name

Console
- brand
- name

ConsoleVersion
- console
- name (e.g. Slim, Digital Edition, PAL)
- condition
- prijs (hidden field)
- foto (blob in database)

Game
- consoles (multiple)
- name

GameVersion
- game
- name (e.g. Physical, Digital, GOTY, PAL)
- condition
- prijs (hidden field)
- foto (blob in database)

## Setup

```bash
# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Start dev server
symfony server:start
```

Open http://127.0.0.1:8000

Set `MAILER_DSN` and `MAILER_FROM` in `.env.local` to send password reset emails (e.g. `smtp://user:pass@smtp.example.com:587`).

## Usage
1. Register the first account, or register via an invite link from an existing user
2. Use **Forgot password?** on the login page to reset via email
3. Add brands (Nintendo, Sony, etc.)
4. Add consoles linked to a brand, then add versions per console
5. Add games and link them to one or more consoles, then add versions per game
6. Use **Invites** to invite new users
7. Use **Friends** to copy your connect link; the other person opens it in a new tab while logged in to connect
8. View a friend's collection read-only from **Friends**
9. Use **Pick console** to browse games per console, or **Pick random console**
10. Use **Pick a game** for a random version from your own collection
