require "bundler/setup"

task :default => :test

desc "Publish \"marketing\" docs"
task :publish do
  sh("git rebase master gh-pages")
  sh("git checkout master")
  sh("git push origin master")
  sh("git push origin gh-pages")
  sh("git push --tags")
  sh("npm publish")
end

desc "Open your default browser with the test page"
task :test do
  sh("open test/index.html")
end
