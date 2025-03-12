var BackendIpLogin = (function () {
    function BackendIpLogin() {
    }
    BackendIpLogin.prototype.htmlToElement = function (html) {
        var template = document.createElement('template');
        html = html.trim();
        template.innerHTML = html;
        return template.content.firstChild;
    };
    BackendIpLogin.prototype.extendLoginForm = function () {
        var element = document.getElementById('typo3-login-form');
        if (element !== null) {
            var newElement = this.htmlToElement("\n                <div class=\"logincontainer\">\n                    <!-- Nav tabs -->\n                    <ul class=\"nav nav-tabs text-center nav-fill\" role=\"tablist\">\n                        <li role=\"presentation\" class=\"nav-item\">\n                            <button type=\"button\"\n                                    class=\"nav-link active\"\n                                    data-bs-toggle=\"tab\"\n                                    data-bs-target=\"#users\"\n                                    aria-controls=\"users\"\n                                    role=\"tab\">\n                                Users\n                            </button>\n                        </li>\n                        <li role=\"presentation\" class=\"nav-item\">\n                            <button type=\"button\"\n                                    class=\"nav-link\"\n                                    data-bs-toggle=\"tab\"\n                                    data-bs-target=\"#loginform\"\n                                    aria-controls=\"loginform\"\n                                    role=\"tab\">\n                                Login\n                            </button>\n                        </li>\n                    </ul>\n                    <!-- Tab panes -->\n                    <div class=\"tab-content\">\n                        <div role=\"tabpanel\" class=\"tab-pane active\" id=\"users\">\n                            <!-- Users content -->\n                        </div>\n                        <div role=\"tabpanel\" class=\"tab-pane\" id=\"loginform\">\n                            <!-- Login form content -->\n                        </div>\n                    </div>\n                </div>\n            ".trim());
            element.insertBefore(newElement, element.firstChild);
        }
        var loginForm = document.getElementById('loginform');
        if (loginForm !== null) {
            loginForm.insertBefore(document.getElementById('t3-login-submit-section'), loginForm.firstChild);
            loginForm.insertBefore(document.getElementById('t3-login-password-section'), loginForm.firstChild);
            loginForm.insertBefore(document.getElementById('t3-login-username-section'), loginForm.firstChild);
        }
    };
    BackendIpLogin.prototype.setLoginButtonBehaviour = function () {
        var usernameField = document.getElementById('t3-username');
        var passwordField = document.getElementById('t3-password');
        var loginForm = document.getElementById('typo3-login-form');
        document.addEventListener('click', function (event) {
            if (this.activeElement instanceof HTMLButtonElement) {
                var element = this.activeElement;
                if (element.classList.contains('btn-autologin')) {
                    usernameField.value = element.textContent !== null ? element.textContent : '';
                    passwordField.value = '';
                    loginForm.setAttribute('novalidate', '');
                    loginForm.submit();
                    event.preventDefault();
                }
            }
        });
    };
    BackendIpLogin.prototype.setTabFunctionality = function () {
        document.querySelectorAll('ul.nav > li > a[href^="#"]').forEach(function (tabElement) {
            tabElement.addEventListener('click', function () {
                var tabSelector = tabElement.getAttribute('href');
                var targetedTab = document.querySelector(tabSelector);
                if (targetedTab !== null) {
                    var targetedRole = (targetedTab.getAttribute('role') !== null ? targetedTab.getAttribute('role') : '');
                    document.querySelectorAll("div[role=\"".concat(targetedRole, "\"]")).forEach(function (element) {
                        if (element.classList.contains('active')) {
                            element.classList.remove('active');
                        }
                    });
                    if (!targetedTab.classList.contains('active')) {
                        targetedTab.classList.add('active');
                    }
                }
                var parentListElement = tabElement.parentElement;
                var tabRole = parentListElement.getAttribute('role');
                document.querySelectorAll("ul > li[role=\"".concat(tabRole, "\"]")).forEach(function (element) {
                    if (element.classList.contains('active')) {
                        element.classList.remove('active');
                    }
                });
                if (!parentListElement.classList.contains('active')) {
                    parentListElement.classList.add('active');
                }
            });
        });
    };
    return BackendIpLogin;
}());
document.addEventListener('DOMContentLoaded', function () {
    var backendIpLogin = new BackendIpLogin();
    backendIpLogin.extendLoginForm();
    backendIpLogin.setLoginButtonBehaviour();
    backendIpLogin.setTabFunctionality();
    var userForm = document.getElementById("users");
    if (userForm) {
        document.querySelectorAll('div#backend-ip-login-accounts > div.backend-ip-login-account[data-username]').forEach(function (account) {
            var accountElement = backendIpLogin.htmlToElement('<button type="button" class="btn btn-block btn-login btn-autologin">' + account.dataset.username + '</button>');
            userForm.insertBefore(accountElement, userForm.firstChild);
        });
    }
});
