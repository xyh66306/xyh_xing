// common/pushService.js

class PushService {
    constructor() {
        this.isInitialized = false;
        this.clientId = '';
    }

    /**
     * 初始化推送服务
     */
    init() {
        return new Promise((resolve, reject) => {
            if (this.isInitialized) {
                resolve(this.clientId);
                return;
            }

            // #ifdef APP-PLUS
            try {
                // 获取客户端信息
                plus.push.getClientInfo((info) => {
                    if (info && info.clientid) {
                        this.clientId = info.clientid;
                        this.isInitialized = true;
                        console.log('推送服务初始化成功，clientId:', this.clientId);
                        resolve(this.clientId);
                    } else {
                        reject(new Error('获取客户端信息失败'));
                    }
                });
            } catch (error) {
                console.error('推送服务初始化失败:', error);
                reject(error);
            }
            // #endif

            // #ifndef APP-PLUS
            reject(new Error('仅在APP环境下支持推送服务'));
            // #endif
        });
    }

    /**
     * 获取客户端ID
     */
    getClientId() {
        return this.clientId;
    }

    /**
     * 发送本地通知
     * @param {Object} options - 通知选项
     * @param {String} options.title - 标题
     * @param {String} options.content - 内容
     * @param {String} options.payload - 附加数据
     */
    sendLocalNotification(options = {}) {
        // #ifdef APP-PLUS
        const {
            title = '通知',
            content = '',
            payload = '{}'
        } = options;

        plus.push.createMessage(content, payload, {
            title: title,
            sound: 'system',
            badge: true
        });
        // #endif
    }

    /**
     * 清除所有通知
     */
    clearAllNotifications() {
        // #ifdef APP-PLUS
        plus.push.clear();
        // #endif
    }

    /**
     * 设置角标数
     * @param {Number} num - 角标数量
     */
    setBadge(num = 0) {
        // #ifdef APP-PLUS
        plus.runtime.setBadgeNumber(num);
        // #endif
    }

    /**
     * 解析推送消息
     * @param {Object} msg - 推送消息对象
     * @returns {Object} 解析后的消息数据
     */
    parsePushMessage(msg) {
        try {
            let payload = {};
            if (typeof msg.payload === 'string') {
                payload = JSON.parse(msg.payload);
            } else {
                payload = msg.payload || {};
            }
            return {
                title: msg.title || '',
                content: msg.content || '',
                payload: payload,
                type: payload.type || 'notification'
            };
        } catch (error) {
            console.error('解析推送消息失败:', error);
            return {
                title: msg.title || '',
                content: msg.content || '',
                payload: {},
                type: 'notification'
            };
        }
    }
}

// 创建单例实例
const pushService = new PushService();

export default pushService;