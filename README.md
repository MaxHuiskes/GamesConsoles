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
- createdAt

Console
- brand
- name
- createdAt

ConsoleVersion
- console
- name (e.g. Slim, Digital Edition, PAL)
- condition
- description (optional)
- createdAt
- prijs (hidden field)
- foto (blob in database)

Tag
- owner (user)
- name (e.g. co-op, backlog, completed)
- createdAt

Game
- owner (user)
- consoles (multiple, optional)
- console versions (multiple, optional)
- tags (multiple, optional)
- name
- createdAt

GameVersion
- game
- name (e.g. Physical, Digital, GOTY, PAL)
- condition
- description (optional)
- createdAt
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

## Usage
1. Register the first account, or register via an invite link from an existing user
2. Add brands (Nintendo, Sony, etc.)
3. Add consoles linked to a brand, then add versions per console
4. Add games and link them to one or more consoles, then add versions per game
5. Open **Dashboard** for recently added items and per-brand stats
6. Use **Invites** to invite new users
7. Use **Friends** to copy your connect link; the other person opens it in a new tab while logged in to connect
8. View a friend's collection read-only from **Friends**, or **Compare** to see shared and unique games/consoles
9. Use **Pick a brand** to browse consoles per brand, then games per console
10. Use **Pick console** to browse games per console, or **Pick random console**
11. Use **Pick a game** for a random version from your own collection
12. Use **What can I play?** to pick a random version filtered by console and/or condition
