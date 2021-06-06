/* vim: set colorcolumn=: */
/**
 * Prune User Sessions
 *
 * @copyright (c) 2021 Carl Bennett, All Rights Reserved
 * @license MIT
 */
def job_token = ''
def job_url = 'https://tools.carlbennett.me/backend_task/prune_user_sessions'
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
