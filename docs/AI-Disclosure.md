AI Summary

## AI Assistance Disclosure - Mosunna Landais

This project was developed with assistance from generative AI tools:
- **Tool**: Claude Sonnet 5 (Anthropic)
- **Dates**: September 17–20, 2026
- **Scope**: Structuring the Contacts main page (`contactsmain.html`) 
  to support dynamic content that the user will eventually add, instead of hardcoded content.
  Asked how to represent a new user's empty contact list 
  (an empty-state message shown by default) versus a populated one 
  (using an HTML `<template>` element as a pattern for JavaScript to 
  clone per contact, rather than hardcoding sample contact rows into 
  the page). Also covered the profile/avatar placeholder design 
  (`style.css`), converting a blank colored circle into one that 
  displays a contact's first/last initials via CSS flexbox centering, 
  with the actual initials text left for JavaScript to populate from 
  real contact data.
- **Use**: AI generated the HTML/CSS structure and explained the 
  reasoning (why a `<template>` tag instead of hardcoded rows.

## AI Assistance Disclosure - Katherina Dayaon

This project was developed with assistance from generative AI tools:
- **Tools**: Claude Opus 5.5 (Anthropic), Cursor (AI code editor)
- **Dates**: September 26–27, 2026
- **Scope**: Debugging why our API endpoints worked on the live site 
  but returned an "unauthenticated user" error when tested on 
  SwaggerHub. Claude explained that our endpoints relied on PHP 
  sessions, and that SwaggerHub runs requests from a different 
  origin, so the browser never sends our session cookie. We first 
  tried documenting cookie authentication in the Swagger spec and 
  passing the `PHPSESSID` value through SwaggerHub's Authorize 
  dialog, and confirmed with curl that the session itself was valid. 
  When that still failed (browsers block pages from setting the 
  Cookie header), we decided to make the contact endpoints stateless 
  by passing `user_id` on each request, matching the COP4331 LAMP 
  API template. Claude wrote the prompt describing these changes, 
  and Cursor implemented them across the PHP endpoints (`add.php`, 
  `edit.php`, `delete.php`, `list.php`, `search.php`), the frontend 
  JavaScript, and the Swagger `.yaml` file.
- **Use**: AI diagnosed the error, generated the `.yaml` 
  documentation and the code changes, and reviewed the updated 
  `.yaml` against our previous SwaggerHub spec, flagging that the 
  old spec's field names were out of sync with our code and that 
  the example values needed to be replaced with real test data. 
  We reviewed the changes, deployed them to our droplet, and 
  tested each endpoint in SwaggerHub and on the live site.

## AI Assistance Disclosure - Maria Polanco
This project was developed with assistance from generative AI tools:

- **Tool**: Claude Sonnet 5 (Anthropic, claude.ai)
- **Dates**: September 26–27, 2026
- **Scope**: Design and implementation of the soft-delete Trash feature — 
  database schema changes (is_deleted/deleted_at columns), modification of 
  delete.php from a hard DELETE to a soft-delete UPDATE, filtering of 
  list.php and search.php to exclude trashed contacts, creation of a new 
  trash-list.php endpoint to retrieve trashed contacts, and the empty-trash.php 
  endpoint for permanent deletion. 
- **Use**: Generated the initial SQL for the schema changes and the PHP code 
  for delete.php, trash-list.php, and empty-trash.php (reviewed and tested 
  against the team's existing codebase conventions before committing); 


## AI Assistance Disclosure - Isabella Sanglade


This project was developed with assistance from generative AI tools:
- **Tool**: Claude Opus 5.5 (Anthropic, claude.ai)
- **Dates**: September 27, 2026
- **Scope**: Building the Log In (`logIn.html`) and Sign Up (`signUp.html`) 
  pages from our Figma mockups. I provided screenshots of the Figma designs, 
  and Claude generated the HTML structure for both forms: labeled email 
  and password inputs, the "Type password again" field, and the password 
  requirements list shown under it. On the Sign 
  Up page, Claude added built-in HTML validation (`type="email"`, 
  `required`, `minlength`, and a `pattern` attribute) so the browser 
  enforces the listed password requirements (8+ characters, upper and 
  lower case, a number, and a special character) before the form submits.
- **Use**: AI generated the HTML for both pages. It explained which 
  parts HTML can handle on its own and which need more work: checking 
  that the two password fields match requires JavaScript, and the form 
  `action` values are placeholders until the pages are connected to our 
  API. 


All AI-generated code was reviewed, tested against the live server, and 
integrated to match the existing API response conventions and database 
schema established by the rest of the team. Final implementation reflects 
my understanding of the soft-delete pattern and how it integrates with 
the team's existing search/list/delete endpoints.
