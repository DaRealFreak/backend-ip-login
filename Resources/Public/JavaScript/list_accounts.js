"use strict";
class BackendIpLogin {
    htmlToElement(html) {
        const template = document.createElement('template');
        html = html.trim();
        template.innerHTML = html;
        return template.content.firstChild;
    }
    extendLoginForm() {
        const element = document.getElementById('typo3-login-form');
        if (element !== null) {
            const newElement = this.htmlToElement(`
                <div class="logincontainer">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs text-center nav-fill" role="tablist">
                        <li role="presentation" class="nav-item">
                            <button type="button"
                                    class="nav-link active"
                                    data-bs-toggle="tab"
                                    data-bs-target="#users"
                                    aria-controls="users"
                                    role="tab">
                                Users
                            </button>
                        </li>
                        <li role="presentation" class="nav-item">
                            <button type="button"
                                    class="nav-link"
                                    data-bs-toggle="tab"
                                    data-bs-target="#loginform"
                                    aria-controls="loginform"
                                    role="tab">
                                Login
                            </button>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="users">
                            <!-- Users content -->
                        </div>
                        <div role="tabpanel" class="tab-pane" id="loginform">
                            <!-- Login form content -->
                        </div>
                    </div>
                </div>
            `.trim());
            element.insertBefore(newElement, element.firstChild);
        }
        const loginForm = document.getElementById('loginform');
        if (loginForm !== null) {
            loginForm.insertBefore(document.getElementById('t3-login-submit-section'), loginForm.firstChild);
            loginForm.insertBefore(document.getElementById('t3-login-password-section'), loginForm.firstChild);
            loginForm.insertBefore(document.getElementById('t3-login-username-section'), loginForm.firstChild);
        }
    }
    setLoginButtonBehaviour() {
        const usernameField = document.getElementById('t3-username');
        const passwordField = document.getElementById('t3-password');
        const loginForm = document.getElementById('typo3-login-form');
        document.addEventListener('click', function (event) {
            if (this.activeElement instanceof HTMLButtonElement) {
                const element = this.activeElement;
                if (element.classList.contains('btn-autologin')) {
                    usernameField.value = element.textContent !== null ? element.textContent : '';
                    passwordField.value = '';
                    loginForm.setAttribute('novalidate', '');
                    loginForm.submit();
                    event.preventDefault();
                }
            }
        });
    }
    setTabFunctionality() {
        document.querySelectorAll('ul.nav > li > a[href^="#"]').forEach((tabElement) => {
            tabElement.addEventListener('click', function () {
                const tabSelector = tabElement.getAttribute('href');
                const targetedTab = document.querySelector(tabSelector);
                if (targetedTab !== null) {
                    const targetedRole = (targetedTab.getAttribute('role') !== null ? targetedTab.getAttribute('role') : '');
                    document.querySelectorAll(`div[role="${targetedRole}"]`).forEach((element) => {
                        if (element.classList.contains('active')) {
                            element.classList.remove('active');
                        }
                    });
                    if (!targetedTab.classList.contains('active')) {
                        targetedTab.classList.add('active');
                    }
                }
                const parentListElement = tabElement.parentElement;
                const tabRole = parentListElement.getAttribute('role');
                document.querySelectorAll(`ul > li[role="${tabRole}"]`).forEach((element) => {
                    if (element.classList.contains('active')) {
                        element.classList.remove('active');
                    }
                });
                if (!parentListElement.classList.contains('active')) {
                    parentListElement.classList.add('active');
                }
            });
        });
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const backendIpLogin = new BackendIpLogin();
    backendIpLogin.extendLoginForm();
    backendIpLogin.setLoginButtonBehaviour();
    backendIpLogin.setTabFunctionality();
    const userForm = document.getElementById("users");
    if (userForm) {
        document.querySelectorAll('div#backend-ip-login-accounts > div.backend-ip-login-account[data-username]').forEach(function (account) {
            const accountElement = backendIpLogin.htmlToElement('<button type="button" class="btn btn-block btn-login btn-autologin">' + account.dataset.username + '</button>');
            userForm.insertBefore(accountElement, userForm.firstChild);
        });
    }
});
