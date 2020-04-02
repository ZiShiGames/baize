var g2f = function() {
    var h = "";
    var v;
    var aa;
    var V;
    var N = "http://1251010508.cdn.myqcloud.com/1251010508/passa";
    var E = navigator.userAgent;
    var ab = (E.indexOf("Android") > -1);
    var I;
    var A = false;
    var f;
    var Q;
    var q;
    var R;
    var isIos = (E.indexOf("iPhone") > -1 || E.indexOf("iPad") > -1);
    var k = {};
    var S;
    var r;
    var Z;
    var c;
    var w;
    var g;
    var y;
    var a;
    var p;
    var download;
    var u = {
        SHOWRECHARGE: "msg_recharge",
        GETORDERNO: "msg_get_order_no",
        RETURNORDERNO: "mgs_ret_order_no",
        RECHARGE_CALLBACK: "msg_recharge_cb",
        SET_SHARE: "msg_share_init",
        INIT: "msg_init",
        SHOWQRCODE: "msg_qr",
        SHARE_CALLBACK: "msg_share_cb",
        REPORTDATA: "msg_report",
        SENDTODESKTOP: "msg_send_desktop",
        SENDTODESKTOP_CALLBACK: "msg_send_desktop_cb",
        FOCUS_GETSTATE: "msg_get_foucus",
        FOCUS_RETURNSTATE: "msg_ret_focus",
        RECHARGE_PAY: "msg_pay",
        CHECKSHARE: "msg_check",
        RETURNSHARE: "msg_ret_share",
        INIT_CALLBACK: "msg_init_cb",
        ON_LOGINERROR: "msg_error",
        CHECKDOWNLOAD: "msg_check_download",
        RETURNDOWNLOAD: "msg_download",
        WEIBO_SHARE: "msg_weibo_share",
    };
    var getParameter = function(af) {
        var ad = location.search;
        var ag = ad.substr(1, ad.length - 1).split("&");
        for (var ae = 0; ae < ag.length; ae++) {
            if ((ag[ae].split("="))[0] == af) {
                return ag[ae].split("=")[1]
            }
        }
    };
    var getParameters = function() {
        var ad = location.search;
        var ag = {};
        if (!ad.length) {
            return ag
        }
        var af = ad.substr(1, ad.length - 1).split("&");
        for (var ae = 0; ae < af.length; ae++) {
            ag[(af[ae].split("="))[0]] = af[ae].split("=")[1]
        }
        return ag
    };
    var U = function() {
        return getParameter("passIds")
    };
    var z = function(ae, af) {
        var ad = document.createElement("iframe");
        ad.src = ae;
        ad.width = "100%";
        ad.height = "100%";
        ad.style.margin = "auto";
        ad.style.position = "absolute";
        ad.style.top = "0";
        ad.style.left = "0";
        ad.style.bottom = "0";
        ad.style.right = "0";
        ad.style.backgroundColor = "aliceblue";
        ad.id = af;
        aa = af;
        document.body.appendChild(ad);
        return ad
    };
    var postMsg = function(ag, af) {
        var ad = {};
        ad.identify = "g2460";
        ad.msg = ag;
        if (af) {
            ad.data = af
        }
        try {
            parent.window.postMessage(ad, "*")
        } catch (ae) {
            console.log(ae)
        }
    };
    var b = function(af, ad) {
        var ag = document.createElement("div");
        ag.id = "toast";
        ag.style.width = "260px";
        ag.style.height = "130px";
        ag.style.margin = "auto";
        ag.style.position = "absolute";
        ag.style.top = "0";
        ag.style.bottom = "0";
        ag.style.right = "0";
        ag.style.left = "0";
        ag.innerText = af;
        ag.classList.add("toast");
        var ae = document.getElementById("gbox") || document.body;
        ae.appendChild(ag);
        setTimeout(function() {
            ae.removeChild(ag)
        }, ad || 1000)
    };
    var object2Search = function(ag) {
        if (typeof ag != "object") {
            console.error("参数不合法");
            return
        }
        var ae = "?";
        var af = Object.keys(ag);
        for (var ad = 0; ad < af.length; ad++) {
            if (ad == 0) {
                ae += (af[ad] + "=" + ag[af[ad]])
            } else {
                ae += "&" + (af[ad] + "=" + ag[af[ad]])
            }
        }
        return ae
    };
    var C = function() {
        return location.search
    };
    var Y = function(ad, ae) {
        localStorage.setItem(ad, ae)
    };
    var L = function(ad) {
        localStorage.getItem(ad)
    };
    var W = function(ad) {
        top.window.location.href = ad
    };
    var getDataXHR = function(url, cb, param, contenttype) {
        var param = param || {};
        var type = param.type || "get";
        var data = param.data || null;
        try {
            var xhr = new XMLHttpRequest();
            xhr.open(type, url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    var responseData = JSON.parse(xhr.responseText);
                    if (responseData.c < 0) {
                        toastMsg(responseData.m);
                        return
                    }
                    if (xhr.responseText == "error") {
                        alert("请求" + url + "返回error");
                        return
                    }
                    cb && cb(responseData)
                }
            };
            if (contenttype) {
                try {
                    xhr.setRequestHeader("Content-Type", contenttype)
                } catch (e) {
                    alert(e)
                }
            }
            xhr.send(data)
        } catch (e) {
            console.error("xhr出错", e);
            return false
        }
    };

    function loadScript(ae, af) {
        var ad = document.createElement("script");
        ad.type = "text/javascript";
        ad.onload = function() {
            af()
        };
        ad.onerror = function() {
            ad.parentNode.removeChild(ad);
            setTimeout(function() {
                loadScript(ae, af)
            }, 1000)
        };
        ad.src = ae;
        document.getElementsByTagName("head")[0].appendChild(ad)
    }
    var initMessageListener = function() {
        console.info("初始化消息监听");
        window.addEventListener("message", function(ae) {
            var af = ae.data;
            console.log(af.msg, af.data);
            if (af.identify && af.identify == "g2460") {
                switch (af.msg) {
                    case u.GETORDERNO:
                        console.log("GETORDERNO receive");
                        var ad = S(af.data);
                        postMsg(u.RETURNORDERNO, {
                            orderData: ad,
                            amount: af.data.amount,
                            id: af.data.id
                        });
                        break;
                    case u.RECHARGE_CALLBACK:
                        r(af.data);
                        break;
                    case u.SHARE_CALLBACK:
                        console.log("游戏消息:" + JSON.stringify(af));
                        // alert('SHARE_CALLBACK ' + Z + " af.data " + af.data);
                        try {
                            Z(af.data);
                            // alert('恭喜您成功获取奖励!');
                        } catch (ae) {
                            console.log(ae);
                        }
                        break;
                    case u.SENDTODESKTOP_CALLBACK:
                        c(af.data);
                        break;
                    case u.FOCUS_RETURNSTATE:
                        g(af.data);
                        break;
                    case u.RETURNSHARE:
                        a(af.data);
                        break;
                    case u.RETURNDOWNLOAD:
                        download(af.data);
                        break;
                    case u.INIT_CALLBACK:
                        console.log("INIT_CALLBACK");
                        y(af.data);
                        break
                }
            }
        })
    };
    var init = function(ae, ad, af, ah) {
        if (!p) {
            if (!ae) {
                console.error("appid为空")
            } else {
                this.appid = ae;
                var ag = getParameters();
                // if (ah) {
                // V = "//" + location.host + "/index.php";
                // } else {
                try {
                    //在这里运行代码
                    V = "//"+document.referrer.match(/:\/\/(.[^/]+)/)[1]+"/index.php";
                    console.log(V);
                } catch (err) {
                    //在这里处理错误
                    V = "//h5sdk.zytxgame.com/index.php";
                    console.log(V);
                    console.log(err);
                }
                if('client.ipyaoguai.com'==document.referrer.match(/:\/\/(.[^/]+)/)[1]){
                	 V = "//api.baizegame.com/index.php";
                }
                // console.log(document.referrer.match(/:\/\/(.[^/]+)/)[1]+"/index.php");
//                 V = "//api.baizegame.com/index.php";

                // }
                 console.log("V " + V + " location.host " + document.referrer.match(/:\/\/(.[^/]+)/)[1]);
                getDataXHR(V + "/api/getAppInfo?appId=" + ae, function(an) {
                    var aj = an.d;
                    console.log("an " + JSON.stringify(aj));
                    if (an.c != 0) {
                        b("getAppInfo错误")
                    }
                    console.log("platform " + aj.platform);
                    console.log("game_id " + aj.game_id);
                    console.log("game_login_url " + aj.game_login_url);
                    aj.test = ah;
                    aj.appid = ae;
                    aj.shareInfo = af;
                    aj.server_id = getParameter("_game");
                    y = ad;
                    postMsg(u.INIT, aj);
                    try {
                        var ap = aj[Object.keys(aj)[0]];
                        Q = JSON.parse(ap).passType;
                        f = JSON.parse(ap).loginUrl;
                        R = JSON.parse(ap).gameId || JSON.parse(ap).browserId || JSON.parse(ap).gKey;
                        q = Object.keys(aj)[0];
                    } catch (ak) {
                        alert(ak)
                    }
                })
            }
            p = true;
            initMessageListener()
        } else {
            console.log("多次调用init 不再绑定事件")
        }
    };
    var onloginerror = function() {
        postMsg(u.ON_LOGINERROR, f)
    };
    var passType = function() {
        return this.passType
    };
    var showRecharge = function(ad, af, ae) {
        postMsg(u.SHOWRECHARGE, ad);
        S = af;
        r = ae
    };

    var charge = function(id, amount, callback) {
        S = callback;

        var ad = S({
            amount: amount,
            id: id
        });
        postMsg(u.RETURNORDERNO, {
            orderData: ad,
            amount: amount
        });

    };

    var pay = function(af, ae, ad) {
        postMsg(u.RECHARGE_PAY, {
            orderData: ae,
            amount: af
        });
        r = ad
    };
    var openTopicCircle = function() {
        postMsg(u.QQBROSER_TOPIC)
    };
    var qqborwserLogout = function(ad) {
        postMsg(u.QQBROSER_LOGOUT, ad)
    };
    var sendToDesktop = function(ad) {
        postMsg(u.SENDTODESKTOP);
        c = ad
    };
    var reportData = function(ad) {
        postMsg(u.REPORTDATA, ad)
    };
    var T = function() {};
    var showQrCode = function() {
        postMsg(u.SHOWQRCODE)
    };

    var showShare = function() {
        postMsg(u.SET_SHARE)
    };

    var weiboShare = function(ad) {
        postMsg(u.WEIBO_SHARE, ad)
    };

    var getFocusState = function(ae, ad) {
        var ah = ae.openId;
        var af = ae.openKey;
        var ag = ae.appId;
        g = ad;
        postMsg(u.FOCUS_GETSTATE, {
            openId: ah,
            openKey: af,
            appId: ag
        })
    };
    var setShareCallback = function(ad) {
        Z = ad
    };
    var isOpenShare = function(ad) {
        postMsg(u.CHECKSHARE);
        a = ad
    };

    var isDownloadable = function(ad) {
        postMsg(u.CHECKDOWNLOAD);
        download = ad
    };

    var downloadApp = function() {
        console.log("downloadApp");
    }

    return {
        init: init,
        getParameters: getParameters,
        getParameter: getParameter,
        object2Search: object2Search,
        getDataXHR: getDataXHR,
        postMsg: postMsg,
        showRecharge: showRecharge,
        initMessageListener: initMessageListener,
        loadScript: loadScript,
        isIos: isIos,
        passType: passType,
        onloginerror: onloginerror,
        showQrCode: showQrCode,
        getFocusState: getFocusState,
        setShareCallback: setShareCallback,
        reportData: reportData,
        sendToDesktop: sendToDesktop,
        qqborwserLogout: qqborwserLogout,
        openTopicCircle: openTopicCircle,
        pay: pay,
        isOpenShare: isOpenShare,
        charge: charge,
        showShare: showShare,
        isDownloadable: isDownloadable,
        downloadApp: downloadApp,
        weiboShare: weiboShare
    }
}();
