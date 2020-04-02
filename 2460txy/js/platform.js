var platform = function() {};
/*初始化
 * 2016年9月20日16:16:18
 * */
platform.prototype.init = function() {

};
/*支付
 *
 */
platform.prototype.pay = function() {

};
/*关注状态
 *
 * */
platform.prototype.checkFocus = function() {

};
/*上报数据
 *
 * */
platform.prototype.reportData = function() {

};
/*发送到桌面
 *
 * */
platform.prototype.sendToDesktop = function() {

};
/*显示二维码
 *
 * */
platform.prototype.showQrCode = function() {

};
/*前往论坛
 *
 * */
platform.prototype.openTopicCircle = function() {

};
/*登出
 *
 * */
platform.prototype.logout = function() {

};
/*显示引导分享
 *
 * */
platform.prototype.showShareTip = function() {

};
//是否打开分享
platform.prototype.isOpenShare = function() {

};

platform.prototype.showShare = function() {

};


platform.prototype.isDownloadable = function() {

};


platform.prototype.weiboShare = function() {

};

//登陆失败
platform.prototype.onLoginError = function() {
    top.location.replace(document.referrer);
};
