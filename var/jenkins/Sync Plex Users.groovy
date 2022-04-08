/* vim: set colorcolumn=: */
/**
 * Sync Plex Users
 *
 * @copyright (c) 2022 Carl Bennett, All Rights Reserved
 * @license MIT
 */
def job_token = ''
def job_url = 'https://example.com/task/sync_plex_users'
def job_result = null

node() {
    stage('Get config') {
        // Read Config File
        def config
        try {
            config = readJSON file: 'config.json'
        } catch (FileNotFoundException ex) {
            error("The workspace configuration 'config.json' could not be read (" + ex.getMessage() + ")")
        }

        // Load Variables
        job_token = config.job_token
        job_url = config.job_url
    }
    stage('Execute job') {
        job_result = httpRequest consoleLogResponseBody: true, contentType: 'APPLICATION_FORM', httpMode: 'POST', requestBody: 'auth_token=' + job_token, responseHandle: 'NONE', url: job_url
        job_result = readJSON text: job_result.content
        print job_result
    }
}
