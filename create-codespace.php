<?php
require( __DIR__ . '/vendor/autoload.php' );

/**
 * Script to create a Github repo configured with Pantheon Codespaces from a Pantheon site.
 */

/**
 * Constants
 */
$PANTHEON_CODESPACES_URL = "https://github.com/pantheon-systems/pantheon-codespaces.git";
$SITE_UUID = getenv('site_uuid');
$GH_ACCESS_TOKEN = getenv('GH_ACCESS_TOKEN');
$GITHUB_USER = getenv('GITHUB_USER');
$GITHUB_ORG = getenv('GITHUB_ORG');
$REPO_NAME = getenv('repo_name');

/**
 * Get the Pantheon site info.
 */
$site_info_raw_json = shell_exec("terminus site:info " . $SITE_UUID . " --format=json");
$site_info = json_decode($site_info_raw_json, TRUE);
print_r($site_info) . PHP_EOL;

/**
 * Create the repo.
 */
if (empty($REPO_NAME)) {
    $REPO_NAME = $site_info['name'];
}
$auth = base64_encode($GITHUB_USER . ':' . $GH_ACCESS_TOKEN);
$data = json_encode([
    'name' => $REPO_NAME,
    'private' => TRUE
]);
$api_path = "https://api.github.com/orgs/" . $GITHUB_ORG . "/repos";
print_r("Going to create Github repo using url: " . $api_path . " with name of " . $REPO_NAME . " using data: " . $data . PHP_EOL);

$reply_raw = shell_exec("curl -X POST -H \"Accept: application/vnd.github.v3+json\" --header \"Authorization: Basic $auth\" $api_path -d '$data'");
print_r($reply_raw) . PHP_EOL;
$reply = json_decode($reply_raw, TRUE);
print_r($reply) . PHP_EOL;
$github_clone_url = $reply['clone_url'];
// Add in the token to the remote.
$github_clone_url = str_replace("https://github.com", "https://$GH_ACCESS_TOKEN@github.com", $github_clone_url);

/**
 * Clone the Pantheon site into workspace.
 */
$pantheon_site_workspace_folder = "/tmp/site";
shell_exec("git clone ssh://codeserver.dev." . $SITE_UUID . "@codeserver.dev." . $SITE_UUID . ".drush.in:2222/~/repository.git $pantheon_site_workspace_folder");

/**
 * Add the new Github remote.
 */
print_r("Going into $pantheon_site_workspace_folder, and adding the 'github'' remote: $github_clone_url");
print_r(PHP_EOL);
shell_exec("cd $pantheon_site_workspace_folder && git remote add github $github_clone_url");
shell_exec("cd $pantheon_site_workspace_folder && git push github");

/**
 * Add the .devcontainer clone from PANTHEON_CODESPACES_URL and Push back up to Github.
 */
shell_exec("cd $pantheon_site_workspace_folder && git submodule add " . $PANTHEON_CODESPACES_URL . " .devcontainer && cd .devcontainer && git checkout main && git add . && git commit -m \"Adding pantheon-codespaces submodule\" && git push github");