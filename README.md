# Anime awards backend Mk. II

[![dependency status](https://img.shields.io/david/Geo1088/awards-nomination-mkii.svg)](https://david-dm.org/geo1088/awards-nomination-mkii)
![built with Vue.js](https://img.shields.io/badge/built_with_Vue.js-4FC08D.svg?logo=vue.js&logoColor=fff)
![runs on RethinkDB](https://img.shields.io/badge/runs_on_RethinkDB-BE465C.svg?logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAYAAAB75n/uAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAC4jAAAuIwF4pT92AAAAB3RJTUUH4wMYEzUJS4ShFwAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAAHFSURBVEjHrZbPi01xFMA/z7zxzCC9RnYaZWFBSkn+AMrKSFaKUsJGs7KR3axILK2UFVuaYlZKM1NsRE3KjjKbmXmhiYyGj81XXaf73p3rfk+dxfl27vdzTvf8+LbUp8BuNibrwA9gAZgHpoHewC/UZf9fvql31G0qZdpSrwNHE28YOA4MFWJYBEaB7oA43wMngA9lGURdLETYU9vqiNpVD6oX1efq75DN2+T3z31VgJU+qbfU8+p6gEzmAvzVhwHwJvpsopk8CvY+YKx40BTwKdhbgE5OwFqVQ1PAzmB/SZoNcDLYr4HvuQBHgCvh7H7dRisr0zH1qvo1lOgzdSj6tyui3ApMAZuBvcA4sB8YCX6zwDngV90MqqSn3lA7/Zqx3eAffE6NtdKkTFeBs8CxNHGLl3WBC5Vh1PzJl0r2wfigeVUXMKzOBch0mq5ZAKiH1J8BcjonAPV2AHxUd+QEbE+XFuVuTgDqqQBYUw/nBLTUJwHyMhVCX0BHXaqxMveoqwFyTx0tPlt2ATfTfDmQZk1xoTwOrfMAmCnY14BbwWc5PczeoV6u+diaKsl6po/vUjvRFjY4fwRelKzNCWASOBMm7as/F836/cal5S0AAAAASUVORK5CYII=)

I dunno a thing to do the stuff

## Usage

Requires Node 10+.

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
	- Genre allocation
	- Shortlist creation
	- Internal ranking and voting
- Host public-facing surveys for nominations, genre allocations, and final votes
- Allow subreddit moderators and awards hosts to manage user roles and view the progression of the awards as appropriate
- Mitigate the risk of human error and information leaks in the awards process
- Generate data for use with the awards presentation site

Stretches:

- Integrate directly with Discord to automate channel access and role management
- Fold the existing awards site into this repo to further automate results display and eliminate the need to generate JSON data

# Technical TODOs

- Implement smart redirects after logging in, e.g. when you point someone to `/auth/reddit`, specify a path they get sent to afterwards, e.g. `/auth/reddit?to=/apply` to log in and then redirect to the juror application form
  - can we do this with the referrer header?
