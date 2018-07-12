require "bundler/setup"

task default: :test

desc "Publish new release"
task :publish do
  sh("git push --tags")
  sh("npm publish")
end

desc "Open your default browser with the test page"
task :test do
  sh("open test/index.html")
end
