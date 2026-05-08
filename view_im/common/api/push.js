// common/api/push.js

import request from '@/common/request.js';

// 这里需要修改request.js导出方式，先创建一个临时的请求方法
function postRequest(url, data) {
    return new Promise((resolve, reject) => {
        uni.request({
            url: 'https://bingocn.wobeis.com/' + url,
            method: 'POST',
            data: data,
            header: {
                'token': uni.getStorageSync('token') || ''
            },
            success: (res) => {
                resolve(res.data);
            },
            fail: (err) => {
                reject(err);
            }
        });
    });
}

export default {
    /**
     * 保存客户端推送信息
     * @param {Object} data - 客户端信息
     * @param {String} data.clientid - 客户端ID
     * @param {String} data.platform - 平台类型
     */
    saveClientInfo(data) {
        return postRequest('/api/push/saveClientInfo', data);
    },

    /**
     * 获取推送配置
     */
    getPushConfig() {
        return postRequest('/api/push/config', {});
    },

    /**
     * 标记消息已读
     * @param {Object} data - 消息信息
     * @param {String} data.messageId - 消息ID
     */
    markMessageRead(data) {
        return postRequest('/api/push/markRead', data);
    }
};