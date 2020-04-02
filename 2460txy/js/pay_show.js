var SUSU={
    payDialog : '<div onclick="payFun()" style="position: fixed;height: 100%;width: 100%;background: rgba(0, 0, 0, .8);z-index: 100;display: block;top: 0;left: 0;cursor: pointer;"></div><div style="margin-left: -260px;width: 520px;height: auto;overflow: hidden;background: #fff;border-radius: 6px;position: absolute;left: 50%;top: 100px;    z-index: 999999;"><div style="width: 100%;height: 4rem;line-height: 4rem;color: #232323;font-size: 2rem;text-align: center;background-color: #f9ce48;">支付</div><div id="payCloses" onclick="payFun()" style="color: #5c6280;line-height: 3.5rem;text-align: center;height: 4rem;width: 4rem;font-size: 34px;position: absolute; top: 0; right: 0; z-index: 999; cursor:pointer;opacity:0.5;"></div><div style="padding: 1rem;color: red;font-size: 3rem;text-align: center;background-color: #f7f7f7;"><div id="moneyModal">¥10</div><div style="width: 94%;margin: 0 auto;border-bottom: 1px solid #dfdfdf;"></div><div style="font-size: 1.4rem;padding-top: 0.6rem;color: #232323;">请选择支付方式</div></div><div onclick="payAli()" style="height: 6rem;cursor: pointer;position: relative;"><div style="width: 20px;height: 20px;border-top: 2px solid #c8c8cc;border-right: 2px solid #c8c8cc;transform: rotate(45deg);position: absolute;right: 4%;top: 50%;margin-top: -10px;"></div><div style="width: 22%;height: 6rem;float: left;padding-right: 3%;position: relative;"><img src="http://h5sdk.cdn.zytxgame.com/img/icon/Ali.jpg" alt="icon 支付宝" style="width: 54px;height: 54px;margin-top: 1.4rem;float: right;"></div><dl style="width: 75%;height: 6rem;float: left;margin: 0;"><dt style="color: #323232;font-size: 1.4rem;margin: 0;margin-top: 1rem;margin-bottom: 0.4rem;">支付宝支付</dt><dd style="color: #595959;font-size: 1.2rem;margin: 0;">亿万用户的选择，更快更安全</dd></dl></div><div style="width: 94%;margin: 0 auto;border-bottom: 2px solid #dfdfdf;"></div><div onclick="payWX()" style="height: 6rem;cursor: pointer;position: relative;"><div style="width: 20px;height: 20px;border-top: 2px solid #c8c8cc;border-right: 2px solid #c8c8cc;transform: rotate(45deg);position: absolute;right: 4%;top: 50%;margin-top: -10px;"></div><div style="width: 22%;height: 6rem;float: left;padding-right: 3%;position: relative; "><img src="http://h5sdk.cdn.zytxgame.com/img/icon/WX.jpg" alt="icon 微信" style="width: 54px;height: 54px;margin-top: 1.4rem;float: right;"></div><dl style="width: 75%;height: 6rem;float: left;margin: 0;"><dt style="color: #323232;font-size: 1.4rem;margin: 0;margin-top: 1rem;margin-bottom: 0.4rem; ">微信支付</dt><dd style="color: #595959;font-size: 1.2rem;margin: 0;">亿万用户的选择，更快更安全</dd></dl></div>',

}


var payHtml = '<div onclick="payFun()" style="position: fixed;height: 100%;width: 100%;background: rgba(0, 0, 0, .8);z-index: 100;display: block;top: 0;left: 0;cursor: pointer;">';
payHtml += '</div>';
payHtml += '<div style="margin-left: -260px;width: 520px;height: auto;overflow: hidden;background: #fff;border-radius: 6px;position: absolute;left: 50%;top: 100px;    z-index: 999999;">';
payHtml += '<div style="width: 100%;height: 4rem;line-height: 4rem;color: #232323;font-size: 2rem;text-align: center;background-color: #f9ce48;">支付</div>';
payHtml +=
    '<div id="payCloses" onclick="payFun()" style="color: #5c6280;line-height: 3.5rem;text-align: center;height: 4rem;width: 4rem;font-size: 34px;position: absolute; top: 0; right: 0; z-index: 999; cursor:pointer;opacity:0.5;"></div>';
payHtml += '<div style="padding: 1rem;color: red;font-size: 3rem;text-align: center;background-color: #f7f7f7;">';
payHtml += '<div id="moneyModal">¥10</div>';
payHtml += '<div style="width: 94%;margin: 0 auto;border-bottom: 1px solid #dfdfdf;"></div>';
payHtml += '<div style="font-size: 1.4rem;padding-top: 0.6rem;color: #232323;">请选择支付方式</div>';
payHtml += '</div>';
payHtml += '<div onclick="payAli()" style="height: 6rem;cursor: pointer;position: relative;">';
payHtml += '<div style="width: 20px;height: 20px;border-top: 2px solid #c8c8cc;border-right: 2px solid #c8c8cc;transform: rotate(45deg);position: absolute;right: 4%;top: 50%;margin-top: -10px;"></div>';
payHtml += '<div style="width: 22%;height: 6rem;float: left;padding-right: 3%;position: relative;">';
payHtml += '<img src="http://h5sdk.cdn.zytxgame.com/img/icon/Ali.jpg" alt="icon 支付宝" style="width: 54px;height: 54px;margin-top: 1.4rem;float: right;">';
payHtml += '</div>';
payHtml += '<dl style="width: 75%;height: 6rem;float: left;margin: 0;">';
payHtml += '<dt style="color: #323232;font-size: 1.4rem;margin: 0;margin-top: 1rem;margin-bottom: 0.4rem;">支付宝支付</dt>';
payHtml += '<dd style="color: #595959;font-size: 1.2rem;margin: 0;">亿万用户的选择，更快更安全</dd>';
payHtml += '</dl>';
payHtml += '</div>';
payHtml += '<div style="width: 94%;margin: 0 auto;border-bottom: 2px solid #dfdfdf;"></div>';
payHtml += '<div onclick="payWX()" style="height: 6rem;cursor: pointer;position: relative;">';
payHtml += '<div style="width: 20px;height: 20px;border-top: 2px solid #c8c8cc;border-right: 2px solid #c8c8cc;transform: rotate(45deg);position: absolute;right: 4%;top: 50%;margin-top: -10px;"></div>';
payHtml += '<div style="width: 22%;height: 6rem;float: left;padding-right: 3%;position: relative; ">';
payHtml += '<img src="http://h5sdk.cdn.zytxgame.com/img/icon/WX.jpg" alt="icon 微信" style="width: 54px;height: 54px;margin-top: 1.4rem;float: right;">';
payHtml += '</div>';
payHtml += '<dl style="width: 75%;height: 6rem;float: left;margin: 0;">';
payHtml += '<dt style="color: #323232;font-size: 1.4rem;margin: 0;margin-top: 1rem;margin-bottom: 0.4rem; ">微信支付</dt>';
payHtml += '<dd style="color: #595959;font-size: 1.2rem;margin: 0;">亿万用户的选择，更快更安全</dd>';
payHtml += '</dl>';
payHtml += '</div>';

//创建div
var oDiv = document.createElement("div");
var oBody = document.getElementsByTagName("body")[0];
oDiv.setAttribute("id", "payModal");
oDiv.innerHTML = SUSU.payDialog;
oBody.appendChild(oDiv);

payModal.style.display = "none";

// css加伪元素
function loadStyleString(css) {
    var style = document.createElement("style");
    style.type = "text/css";
    try {
        style.appendChild(document.createTextNode(css));
    } catch (ex) {
        style.styleSheet.cssText = css;
    }
    var head = document.getElementsByTagName('head')[0];
    head.appendChild(style);
}
loadStyleString("#payCloses::before{content:'\\00D7';}");

// 调起支付宝支付
function payAli() {
    console.log("支付宝充值，OK");
}

// 调起微信支付
function payWX() {
    console.log("微信充值，OK");
}

// 显示/隐藏 支付页面
function payFun() {
    var payModal = document.getElementById('payModal');
    if (payModal.style.display.match("none")) {
        payModal.style.display = "block";
        return true;
    } else {
        payModal.style.display = "none";
        return false;
    }
}
