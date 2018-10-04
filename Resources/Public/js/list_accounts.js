/**
 * @param html string
 */
function htmlToElement(html) {
    let template = document.createElement('template');
    html = html.trim(); // Never return a text node of whitespace as the result
    template.innerHTML = html;
    return template.content.firstChild;
}

function extendLoginForm() {
    let element = document.getElementById('typo3-login-form');
    element.insertBefore(htmlToElement(`
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
`), element.firstChild);

    // copy original login code to the loginform tab pane
    let loginform = document.getElementById("loginform");
    loginform.insertBefore(document.getElementById("t3-login-submit-section"), loginform.firstChild);
    loginform.insertBefore(document.getElementById("t3-login-password-section"), loginform.firstChild);
    loginform.insertBefore(document.getElementById("t3-login-username-section"), loginform.firstChild);
}

function addLoginButtonBehaviour() {
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-autologin')) {
            console.log(e.target);
            document.getElementById("t3-username").value = e.target.textContent;
            document.getElementById("t3-password").value = "";
            document.getElementById('typo3-login-form').setAttribute('novalidate', true);
            document.getElementById("t3-login-submit").click();
            e.preventDefault();
        }
    });
}

extendLoginForm();
addLoginButtonBehaviour();

// used in the PageRendererHook to append the users
// noinspection JSUnusedGlobalSymbols
let userform = document.getElementById("users");
