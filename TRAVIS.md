## Running Tests on Travis

[Travis](https://travis-ci.org/) automatically runs tests whenever a github
repo changes.  To have Travis automatically run tests on your forked copy
of this repo:

1.  Fork this repo on [GitHub](https://github.com/).
2.  Visit the
    [Google Developers Console](https://console.developers.google.com/) and
    choose an existing project or create a new project.
3.  Under `APIs & auth`, choose Credentials.
4.  Click `Add credentials`, and then click `Service account`.
5.  Under `Key type`, choose `JSON`, and then click `Create`.  A json credential
    file will be downloaded to your computer.
6.  Visit [Travis](https://travis-ci.org/profile ) and turn on Travis for your
    new forked repo.
7.  Go back to the [Travis](https://travis-ci.org/) home page, click on your
    repo, then click on `Settings`.
8.  Under Environment Variables, set GOOGLE_PROJECT_ID to the project id
    for the project you created or chose in step 2.
9.  Base-64 encode the json file you downloaded in step 5.  On unix machines,
    this can be done with a command like
    `base64 -w 0 < my-test-bf4af540ca4c.json`. 
10. Under Environment Variables, set GOOGLE_CREDENTIALS_BASE64 to the
    base64-encoded json from step 9.  **Be sure te leave `Display value in build
    log` switched OFF.**
    
