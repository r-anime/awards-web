# frozen_string_literal: true
require "yaml"
require "sinatra"
require "sinatra/namespace"
require "sinatra/json"
require "redd/middleware"
require "rethinkdb"

CONFIG = YAML.load_file "config.yml"
PUBLIC_FOLDER = File.join(File.dirname(__FILE__), "public")

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
  scope: ["identity"]
})

before do
  @r = request.env["redd.session"]
  p @r
  @test = 3
end

# Helper functions
module Helpers
  def h(*yeet)
    Rack::Utils.escape_html(*yeet)
  end

  def public_file(filename)
    File.read(File.join(PUBLIC_FOLDER, filename))
  end
end
helpers Helpers

# API things
namespace "/api" do
  get "/me" do
    return json nil if !@r
    me = @r.me.to_h
    # TODO: how bad practice is it to merge symbol and string keys if we convert
    #       it to json immediately
    me = me.merge({
      isHost: me[:name] == "geo1088",
      isJuror: me[:name] == "geo1088",
      isMod: me[:name] == "geo1088",
    })
    json me
  end

  not_found do
    [404, json(status: 404, message: "Not Found")]
  end
end

# Auth callback
get "/auth/reddit/callback" do
  h request.env["redd.error"].inspect if request.env["redd.error"]
  redirect to "/"
end
get "/auth/reddit/logout" do
  request.env["redd.session"] = nil
  redirect back
end

# Public stuff
get "*" do
  public_file "index.html"
end
