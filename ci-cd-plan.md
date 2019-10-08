# CI/CD Plan

1. Develop new feature on feature branch.
2. (Run test or build or sanity check for each commit) -> can be skipped this time
3. When merging feature branch to master branch, we trigger **CI/CD** process to make production build.
   (current)
   - Just write a script for production server to `git pull` and `docker-compose up -d`?
   (ideal)
   - Each image should be built and push to docker registry.
   - Should write a script for production server to pull those image and start them.
4. A **Deploy** button is provided either in github or circleCI dashboard to deploy onto AWS ec2.