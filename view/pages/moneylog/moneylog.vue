<template>
	<view>
		<view class="list"  @click="openTeam">
			<view class="item u-border-bottom" v-for="(vo,index) in list" :key="index"  @click="openTeam">
				<view class="icon">
					<template v-if="vo.type==1">
						<u-avatar fontSize="14"text="转账" shape="square" randomBgColor></u-avatar>
					</template>
					<template v-else-if="vo.type==2">
						<u-avatar fontSize="14" text="充值" shape="square" randomBgColor></u-avatar>
					</template>										
					<template v-else-if="vo.type==3">
						<u-avatar fontSize="14" text="提现" shape="square" randomBgColor></u-avatar>
					</template>		
					<template v-else-if="vo.type==4">
						<u-avatar fontSize="14" text="获赠" shape="square" randomBgColor></u-avatar>
					</template>		
					<template v-else-if="vo.type==5">
						<u-avatar fontSize="14" text="返佣" shape="square" randomBgColor></u-avatar>
					</template>	
					<template v-else-if="vo.type==6">
						<u-avatar fontSize="14" text="冻结" shape="square" randomBgColor></u-avatar>
					</template>	
					<template v-else-if="vo.type==7">
						<u-avatar fontSize="14" text="兑出" shape="square" randomBgColor></u-avatar>
					</template>		
					<template v-else-if="vo.type==8">
						<u-avatar fontSize="14" text="兑入" shape="square" randomBgColor></u-avatar>
					</template>										
				</view>
				<view class="info">
					<view class="time u-info">
						<template v-if="vo.flow_type==1">
							 <u--text type="primary" :text="vo.usdt"></u--text>
						</template>
						<template v-else>
							<u--text type="warning" :text="-vo.usdt"></u--text>
						</template>
					</view>
					<view class="">{{vo.createtime}}</view>
				</view>
				<view class="money">
					<view class="change u-primary">

					</view>
					<view class="yue">余额: {{vo.after}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				page:1,
				type:'',
				flow_type:'',
				loadStatus: 'more',
				list:[],
				randomBgColor:'',
				open_team:false,//true不显示返佣
				timer:0,
				times: 0,
			}
		},
		onLoad() {
			this.getUsdtLog();
			this.open_team = uni.getStorageSync('open_team') ? true : false;
		},
		onReachBottom() {
			if (this.loadStatus === 'more') {
				this.getUsdtLog();
			}
		},		
		mounted() {
			this.randomBgColor = this.getRandomColor(); // 在组件挂载后设置随机颜色
		},		
		methods: {
			// openTeam() {
			// 	if (this.timer) {
			// 		clearTimeout(this.timer);
			// 	}
				
			// 	this.times++;
			// 	console.log(this.times)
			// 	if (this.times >= 5) {
			// 		this.open_team = !this.open_team;
			// 		uni.setStorageSync('open_team', this.open_team);
			// 		uni.$u.toast(this.open_team ? '显示返佣数据' : '已关闭返佣数据');
			// 		this.times = 0;
			// 		this.list = [];
			// 		this.getUsdtLog();
			// 		return;
			// 	}
			// 	this.timer = setTimeout(() => {
			// 		this.times = 0;
			// 	}, 1000);
			// },	
			openTeam() {
				// 定义常量，提高可维护性
				const CLICK_THRESHOLD = 5;
				const RESET_DELAY = 1000;

				// 清除之前的定时器，防止多次触发导致状态混乱
				if (this.timer) {
					clearTimeout(this.timer);
					this.timer = null; // 显式置空，避免引用残留
				}
				
				// 增加连击计数
				this.times++;
				
				// 移除生产环境下的 console.log，避免性能损耗和信息泄露
				// console.log(this.times); 

				// 检查是否达到连击阈值
				if (this.times >= CLICK_THRESHOLD) {
					// 切换状态
					this.open_team = !this.open_team;
					
					try {
						// 持久化状态
						uni.setStorageSync('open_team', this.open_team);
					} catch (e) {
						// 捕获存储异常，避免程序崩溃
						console.error('Failed to save open_team status:', e);
					}
					
					// 显示提示信息
					uni.$u.toast(this.open_team ? '显示返佣数据' : '已关闭返佣数据');
					
					// 重置计数器
					this.times = 0;
					
					// 清空列表并重新获取数据
					this.list = [];
					
					// 调用获取日志方法，建议在实际项目中确保 getUsdtLog 内部有错误处理
					// 如果 getUsdtLog 是异步的，建议添加 .catch 或 try-await
					if (typeof this.getUsdtLog === 'function') {
						this.page = 1;
						this.getUsdtLog();
					}
					
					return;
				}

				// 设置定时器，在指定时间后重置连击计数
				this.timer = setTimeout(() => {
					this.times = 0;
					this.timer = null; // 定时器执行后置空
				}, RESET_DELAY);
			},					
			getRandomColor() {
			  const letters = '0123456789ABCDEF';
			  let color = '#';
			  for (let i = 0; i < 6; i++) {
				color += letters[Math.floor(Math.random() * 16)];
			  }
			  return color;
			},			
			getUsdtLog(){
				uni.$u.http.post("/api/user/getusdtlog",{
					page:this.page,
					type:this.type,
					flow_type:this.flow_type,
					open_team:this.open_team
				}).then((res)=>{
					if(res.code == 1) {
						const _list = res.data.list;
						this.list = [...this.list, ..._list];
						if (res.data.count > this.list.length) {
							this.loadStatus = 'more';
							this.page++;
						} else {
							// 数据已加载完毕
							this.loadStatus = 'noMore';
						}
					}else{
						uni.$u.toast(res.msg);
					}
				}).catch(res => {
					uni.$u.toast(res.msg);
				});
			}
		}
	}
</script>

<style lang="scss">
	.list {}

	.item {
		padding: 20rpx 30rpx;
		display: flex;
		align-items: center;
	}

	.icon {
		margin-right: 20rpx;
	}

	.info {
		flex: 1;
		font-size: 14px;
		line-height: 20px;
	}

	.time {
		flex: 1;
	}

	.change {
		font-size: 14px;
		margin-bottom: 5rpx;
		display: flex;
		justify-content: flex-end;
	}

	.yue {
		font-size: 14px;
	}
</style>