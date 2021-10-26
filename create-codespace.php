<?php
require( __DIR__ . '/vendor/autoload.php' );

/**
 * Script to create a Github repo configured with Pantheon Codespaces from a Pantheon site.
 */

/**
 * Constants
 */
const PANTHEON_CODESPACES_URL = "https://github.com/pantheon-systems/pantheon-codespaces.git";
const SITE_UUID = get_env('site_uuid');
const GH_ACCESS_TOKEN = get_env('GH_ACCESS_TOKEN');
const GITHUB_USER = get_env('GITHUB_USER');
const GITHUB_ORG = get_env('GITHUB_ORG');
$REPO_NAME = get_env('repo_name');

/**
 * Get the Pantheon site info.
 */
$site_info_raw_json = shell_exec("terminus site:info " . SITE_UUID . " --format=json");
$site_info = json_decode($site_info_raw_json, TRUE);
print_r($site_info) . PHP_EOL;

/**
 * Create the repo.
 */
if (empty($REPO_NAME)) {
    $REPO_NAME = $site_info['name'];
}
$auth = base64_encode(GITHUB_USER . ':' . GH_ACCESS_TOKEN);
$data = json_encode([
    'name' => $REPO_NAME,
]);
$reply_raw = shell_exec("curl -X POST -H \"Accept: application/vnd.github.v3+json\" --header \"Authorization: Basic $auth\" https://api.github.com/orgs/" . GITHUB_ORG . "/repos -d $data");
$reply = json_decode($reply_raw, TRUE);
$github_clone_url = $reply['clone_url'];

/**
 * Clone the Pantheon site into workspace.
 */
$pantheon_site_workspace_folder = "/tmp/site";
shell_exec("git clone ssh://codeserver.dev." . SITE_UUID . "@codeserver.dev." . SITE_UUID . ".drush.in:2222/~/repository.git $pantheon_site_workspace_folder");

/**
 * Add the new Github remote.
 */
shell_exec("cd $pantheon_site_workspace_folder && git remote add github $github_clone_url");

/**
 * Add the .devcontainer clone from PANTHEON_CODESPACES_URL and Push back up to Github.
 */
shell_exec("cd $pantheon_site_workspace_folder && git submodule add " . PANTHEON_CODESPACES_URL . " .devcontainer && cd .devcontainer && git checkout main && git add . && git commit -m \"Adding pantheon-codespaces submodule\" && git push github");