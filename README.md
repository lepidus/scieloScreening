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

* OPS 3.5.0

## Document screening limits

PDF screening requires `pdftotext` (poppler-utils) and `prlimit` (util-linux) on Linux, with PHP process execution enabled. Conversion is limited to one process per plugin installation, 20 MiB of input, 2 MiB of extracted text, 5 seconds of wall time, 5 seconds of CPU time, and 256 MiB of address space. Temporary text files are private, unique, and removed after conversion.

If conversion fails, exceeds a limit, or another conversion is running, the ORCID check is shown as unavailable. It does not establish that the document has no ORCIDs or that its authors have no public works. Moderators can retry the check later or review the document manually.

ORCID requests accept only the four HTTPS Public/Member production and sandbox endpoints offered in the settings form. Previously stored custom URLs are rejected, and HTTP redirects are disabled to keep credentials and bearer tokens on the selected endpoint.

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
