# API things
namespace "/api" do
  def authenticate_reddit!(name: nil)
    halt jsonerr(401, "Unauthorized") if !@redditor
    if name && @redditor.name != name
      halt jsonerr(401, "Unauthorized")
    end
  end

  get "/me" do
    authenticate_reddit!
    json({
      user: @user,
      redditor: @redditor.to_h,
    })
  end

  get "/users" do
    authenticate_reddit!
    p @user
    half jsonerr(401, "Unauthorized") if not @user["mod"]
    users = r.table("users").run.to_a
    json(users)
  end

  get "/juror_applications/:reddit_name" do
    name = params[:reddit_name]
    authenticate_reddit! name: name
    json({
      name: name,
      applied: true,
    })
  end

  put "/juror_applications/:reddit_name" do
    name = params[:reddit_name]
    authenticate_reddit! name: name
    request.body.rewind
    data = JSON.parse request.body.read
    r.table(juror_applications).insert({
      # things
      # TODO
    }, conflict: "update")
  end

  not_found do
    jsonerr 404, "Not Found"
  end
end
