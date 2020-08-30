# [/r/anime Awards Website](https://animeawards.moe/) Mk. II

[![dependency status](https://img.shields.io/david/Geo1088/awards-nomination-mkii.svg)](https://david-dm.org/geo1088/awards-nomination-mkii)
[![devDependencies Status](https://david-dm.org/geo1088/awards-nomination-mkii/dev-status.svg)](https://david-dm.org/geo1088/awards-nomination-mkii?type=dev)
![built with Vue.js](https://img.shields.io/badge/built_with_Vue.js-4FC08D.svg?logo=vue.js&logoColor=fff) [![discord server](https://img.shields.io/discord/682788356265869322.svg)](https://discord.gg/27YaM6W)

Uses [this project](https://github.com/JoseiToAoiTori/animethemes-scraper) to grab themes from /r/AnimeThemes.

## Usage

Requires Node 10+ and SQLite3.

```bash
# Edit your config
$ cp sample.config.js config.js && $EDITOR config.js
# Install dependencies
$ yarn
# Build and run
$ yarn start
# Just build the frontend
$ yarn build
# Just start the server
$ yarn serve
```

## Aims

- Automate the behind-the-scenes processes used by jurors and hosts of the /r/anime awards, including:
	- Juror applications and juror selection
	- Category allocation
	- Shortlist creation
	- Internal ranking and voting
	- Public voting
	- Hosting results of every year
- Host public-facing surveys for nominations, genre allocations, and final votes
- Allow subreddit moderators and awards hosts to manage user roles and view the progression of the awards as appropriate
- Mitigate the risk of human error and information leaks in the awards process
- Generate data for use with the awards presentation front-end.

Stretches:

- Integrate directly with Discord to automate channel access and role management
- Create a juror dashboard to eliminate need for sheets and shortlists
- Build our own internal voting systems rather than rely on Condorcet.

## Stack

This project has two parts: a Vue frontend, and a Node.js API server.

The frontend uses vuex for state management and vue-router for routing. Styling is done primarily with the Bulma framework, with some custom styles applied with Sass. The frontend is compiled with Webpack and served statically via sirv.

The backend uses Polka, a lightweight server framework, to serve the frontend, a JSON API, and some other routes used for OAuth authentication with Reddit. Sequelize is used as an ORM.
