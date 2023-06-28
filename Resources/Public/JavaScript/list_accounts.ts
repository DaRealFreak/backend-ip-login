class BackendIpLogin {
    /**
     * helper function to convert plain HTML to JavaScript elements to insert
     *
     * @param html
     */
    public htmlToElement(html: string): ChildNode | null {
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
    public extendLoginForm(): void {
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
    public setLoginButtonBehaviour(): void {
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

    /**
     * in case of TYPO3 11+ there is no bootstrap.js loaded anymore, so minimalistic functionality for tabs
     */
    public setTabFunctionality(): void {
        document.querySelectorAll('ul.nav > li > a[href^="#"]').forEach((tabElement: HTMLLinkElement) => {
            tabElement.addEventListener('click', function () {
                const tabSelector = tabElement.getAttribute('href') as string
                const targetedTab = document.querySelector(tabSelector)
                if (targetedTab !== null) {
                    const targetedRole = (targetedTab.getAttribute('role') !== null ? targetedTab.getAttribute('role') : '') as string
                    // hide all other elements with the same role
                    document.querySelectorAll(`div[role="${targetedRole}"]`).forEach((element) => {
                        if (element.classList.contains('active')) {
                            element.classList.remove('active')
                        }
                    })

                    // add active class if not already
                    if (!targetedTab.classList.contains('active')) {
                        targetedTab.classList.add('active')
                    }
                }

                const parentListElement = tabElement.parentElement as HTMLElement
                const tabRole = parentListElement.getAttribute('role') as string
                document.querySelectorAll(`ul > li[role="${tabRole}"]`).forEach((element) => {
                    // disable highlighting on all tabs
                    if (element.classList.contains('active')) {
                        element.classList.remove('active')
                    }
                })

                // highlight current tab again
                if (!parentListElement.classList.contains('active')) {
                    parentListElement.classList.add('active')
                }
            })
        })
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const backendIpLogin = new BackendIpLogin()
    backendIpLogin.extendLoginForm()
    backendIpLogin.setLoginButtonBehaviour()
    backendIpLogin.setTabFunctionality()

    const userForm = document.getElementById("users")
    if (userForm) {
        document.querySelectorAll('div#backend-ip-login-accounts > div.backend-ip-login-account[data-username]').forEach(function (account: HTMLElement) {
            let accountElement: ChildNode = backendIpLogin.htmlToElement('<button type="button" class="btn btn-block btn-login btn-autologin">' + account.dataset.username + '</button>') as ChildNode
            userForm.insertBefore(accountElement, userForm.firstChild)
        })
    }
})
