window.onload = function () {
    // sleep for 150 ms since browsers tend to jump to their last scroll position after reloading
    setTimeout(function () {
        document.getElementsByClassName('tab-content')[0].scrollTop = 0
    }, 150)
}
