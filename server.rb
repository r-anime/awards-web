# frozen_string_literal: true
require "yaml"
require "sinatra"
require "sinatra/namespace"
require "sinatra/json"
require "redd/middleware"
require "rethinkdb"

include RethinkDB::Shortcuts

CONFIG = YAML.load_file "config.yml"
PUBLIC_FOLDER = File.join(File.dirname(__FILE__), "public")

# Database connection
r.connect(db: "animeawards_mkii").repl

# Sinatra options
set :bind, "0.0.0.0"
set :port, 4567
set :public_folder, PUBLIC_FOLDER

# Reddit auth middleware
use Rack::Session::Cookie, secret: CONFIG[:cookie_secret]
use Redd::Middleware, CONFIG[:reddit].merge({
  user_agent: "web:github.com/Geo1088/awards-nomination:v1.0.0 (by /u/geo1088)",
  via: "/auth/reddit",
  redirect_uri: "#{CONFIG[:host]}/auth/reddit/callback",
  scope: %w[identity]
})

before do
  @reddit = request.env["redd.session"]
  @redditor = @reddit.me if @reddit
  @user = r.table("users").filter(reddit: @redditor.name)[0].run if @redditor
end

# Helper functions
module Helpers
  def h(*yeet)
    Rack::Utils.escape_html(*yeet)
  end

  def public_file(filename)
    File.read(File.join(PUBLIC_FOLDER, filename))
  end

  def validate(hash, schema)
    ClassyHash.validate hash, schema, strict: true, raise_errors: false
  end

  def jsonerr(status, message, **other)
    [
      status,
      json({
        status: status,
        message: message,
      }.merge(other))
    ]
  end
end
helpers Helpers

# Auth callback
get "/auth/reddit/callback" do
  halt h request.env["redd.error"].inspect if request.env["redd.error"]
  # If the user isn't registered yet, register them
  if !@user
    @r.table("users").insert({
      reddit: @redditor.name,
      discord: nil,
      mod: p(@reddit.subreddit(CONFIG[:subreddit]).moderators.run),
      host: false,
      juror: false,
    }).run
    # We just redirect back to the same place
    redirect to "/auth/reddit/callback"
  end
  redirect to "/"
end
get "/auth/reddit/logout" do
  request.env["redd.session"] = nil
  redirect back
end

# API stuff
require_relative "api/main"

# Public stuff
get "*" do
  public_file "index.html"
end
