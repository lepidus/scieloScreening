# SciELO Screening Plugin

This plugin adds a series of verifications to OPS, performed over authors' submissions. The status of each verification is then displayed at Workflow page, so the moderators can check it.

The verifications performed are:

* The submitter author must add one PDF, and no more than one, as a galley.
* All submission contributors must have their affiliation filled.
* At least one contributor must have his ORCID confirmed.
* The submitter should inform the number of contributors at Contributors Step.
* None of the contributors can have his name filled all in capital letters.
* Title, abstract and keywords must be also filled in English, as well as in the language of submission.

## Compatibility

The latest release of this plugin is compatible with the following PKP applications:

* OPS 3.4.0

## Plugin Download

To download the plugin, go to the [Releases page](https://github.com/lepidus/scieloScreening/releases) and download the tar.gz package of the latest release compatible with your website.

## Installation

1. Enter the administration area of ​​your OPS website through the __Dashboard__.
2. Navigate to `Settings`>` Website`> `Plugins`> `Upload a new plugin`.
3. Under __Upload file__ select the file __scieloScreening.tar.gz__.
4. Click __Save__ and the plugin will be installed on your website.

# License

__This plugin is licensed under the GNU General Public License v3.0__

__Copyright (c) 2020-2024 Lepidus Tecnologia__

__Copyright (c) 2020-2024 SciELO__
