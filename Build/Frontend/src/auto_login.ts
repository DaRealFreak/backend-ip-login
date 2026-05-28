// the behavior during autologin is to check for empty username and empty password
// while the auto login option is enabled for the extension
document.addEventListener('DOMContentLoaded', function () {
    (document.getElementById('t3-login-username-section') as HTMLDivElement).remove();
    (document.getElementById('t3-login-password-section') as HTMLDivElement).remove();
    (document.getElementById('typo3-login-form') as HTMLFormElement).submit()
})
