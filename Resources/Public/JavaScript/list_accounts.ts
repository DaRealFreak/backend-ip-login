class BackendIpLogin {
    /**
     * helper function to convert plain HTML to JavaScript elements to insert
     *
     * @param html
     */
    public htmlToElement (html: string): ChildNode | null {
        const template = document.createElement('template')
        // trim to prevent nodes with whitespaces as the result
        html = html.trim()
        template.innerHTML = html
        return template.content.firstChild
    }

    /**
     * functionality to split login tab into 2 tabs (users and manual login)
     * copies the original login form to the second page
     */
    public extendLoginForm (): void {
        const element = document.getElementById('typo3-login-form')
        if (element !== null) {
            const newElement = this.htmlToElement(`
                <div class="logincontainer">
                    <!-- Nav tabs-->
                    <ul class="nav nav-tabs text-center" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#users" aria-controls="users" role="tab" data-toggle="tab">Users</a>
                        </li>
                        <li role="presentation">
                            <a href="#loginform" aria-controls="login" role="tab"
                               data-toggle="tab">Login</a>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="users">
                        </div>
                        <div role="tabpanel" class="tab-pane" id="loginform">
                        </div>
                    </div>
                </div>
            `.trim()) as ChildNode
            element.insertBefore(newElement, element.firstChild)
        }

        // copy original login code to the login form tab pane
        const loginForm = document.getElementById('loginform')
        if (loginForm !== null) {
            loginForm.insertBefore(document.getElementById('t3-login-submit-section') as HTMLDivElement, loginForm.firstChild)
            loginForm.insertBefore(document.getElementById('t3-login-password-section') as HTMLDivElement, loginForm.firstChild)
            loginForm.insertBefore(document.getElementById('t3-login-username-section') as HTMLDivElement, loginForm.firstChild)
        }
    }

    /**
     * adds the event listener for every user button to empty password (our way to differentiate between manual and auto login)
     * set the username and submit the login form
     */
    public setLoginButtonBehaviour (): void {
        const usernameField: HTMLInputElement = document.getElementById('t3-username') as HTMLInputElement
        const passwordField: HTMLInputElement = document.getElementById('t3-password') as HTMLInputElement
        const loginForm: HTMLFormElement = document.getElementById('typo3-login-form') as HTMLFormElement

        document.addEventListener('click', function (event) {
            if (this.activeElement instanceof HTMLButtonElement) {
                const element = this.activeElement
                if (element.classList.contains('btn-autologin')) {
                    usernameField.value = element.textContent !== null ? element.textContent : ''
                    passwordField.value = ''
                    loginForm.setAttribute('novalidate', '')
                    loginForm.submit()
                    event.preventDefault()
                }
            }
        })
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const backendIpLogin = new BackendIpLogin()
    backendIpLogin.extendLoginForm()
    backendIpLogin.setLoginButtonBehaviour()
})
