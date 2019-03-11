# Anime awards backend Mk. II

I dunno a thing to do the stuff

## Usage

Requires Ruby 2.6 and Node 10+.

```bash
# Install frontend dependencies
$ yarn # with yarn
$ npm install # with npm
# Build the frontend (add --watch for development)
$ npx webpack
# Install server dependencies
$ bundle install
# Run the server
$ bundle exec ruby server.rb
```

## Aims

- Automate the behind-the-scenes processes used by jurors and hosts of the /r/anime awards, including:
	- Genre allocation
	- Shortlist creation
	- Internal ranking and voting
- Host public-facing surveys for nominations, genre allocations, and final votes
- Allow subreddit moderators to manage the role of various users, including host selection and juror selection
- Mitigate the risk of human error and information leaks in the awards process
- Generate data for use with the awards presentation site
