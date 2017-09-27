/**
 * Created by Steffen on 13/05/2017.
 */
// move username and password section into a new tab pane
$('#typo3-login-form').prepend(`
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
`);

const loginform = $("#loginform");
const userform = $("#users");
loginform.prepend($("#t3-login-submit-section"));
loginform.prepend($("#t3-login-password-section"));
loginform.prepend($("#t3-login-username-section"));


$(function(){
    $('#users > button').click(function(e) {
        $("#t3-username").val($(this).html());
        $("#t3-password").val("");
        $('#typo3-login-form').attr('novalidate', 'novalidate');
        $("#t3-login-submit").click();
        e.preventDefault();
    });
});