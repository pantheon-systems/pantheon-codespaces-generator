# pantheon-codespaces-generator
A generator to import Pantheon sites into a Github repo with pantheon-codespaces configured.

[![Unofficial](https://img.shields.io/badge/Pantheon-Unofficial-yellow?logo=pantheon&color=FFDC28)](https://pantheon.io/docs/oss-support-levels#unofficial)

This repo contains an Action definition that can be triggered via workflow_dispatch. The parameters are:
- `site_uuid` - The site ID for the Pantheon site. You can find this when on your Pantheon site dashboard in the URL - something like "https://dashboard.pantheon.io/sites/a31e101e-bebf-4f03-874c-629a7g752ac1" - the site UUID is "a31e101e-bebf-4f03-874c-629a7g752ac1".
- `repo_name` - Optional: The Github repo name to use. The default will be the site machine name. If the repo already exists the process will fail.
- `GITHUB_ORG` - The Github org name to use for creating the repo.
- `GITHUB_USER` - The Github user to use, defaults to the user calling the Action.

The configuration secrets for the repo required are:
- `SSH_KEY` - A private SSH key with a public equivalent added to Pantheon dashboard - generated via `ssh-keygen -p -m PEM` - the private key should be used as the `SSH_KEY` secret, and the public key should be added to the Pantheon account to be used.
- `SSH_KNOWN_HOSTS` - Can be empty by default or the contents of a `known_hosts` file if desired for advanced SSH config, or use the default:
```
127.0.0.1
```
- `TERMINUS_MACHINE_TOKEN` - A machine token generated from Pantheon dashboard for the account to be used.
- `GH_ACCESS_TOKEN` - A personal access token with access to create and write to a Github repo in the same organization to be used.

## Usage via web:
- Fork the repo
- Configure the secrets, and make sure Actions are enabled
- Go to the Actions tab, go to the "Create new Pantheon site Github repo" Action
 ![image](https://user-images.githubusercontent.com/6699959/138955981-fc224d03-d91e-43c4-9e37-eee64546bd51.png)

- Add the Pantheon site ID and click Run
- You can watch the Action running in the Actions tab, and check for any errors if failures are encountered
- Once completed, you should have a Github repo created with the Pantheon machine name as the name of the repo, and pantheon-codespaces configured in the repo
