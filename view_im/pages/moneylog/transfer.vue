<template>
	<view>
		<view class="list">
			<view class="item u-border-bottom" v-for="(vo,index) in list" :key="index">
				<view class="icon">
					<template v-if="vo.flow_type==2">
						<u-avatar text="出" randomBgColor></u-avatar>
					</template>
					<template v-else-if="vo.flow_type==1">
						<u-avatar text="入" randomBgColor></u-avatar>
					</template>																								
				</view>
				<view class="info">
					<view class="time u-info">{{vo.createtime}}</view>
					<view class="">{{vo.beizhu  }}</view>
				</view>
				<view class="money">
					<view class="change u-primary">{{vo.usdt}}</view>
					<view class="yue">余额 {{vo.after}}</view>
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
				type:1,
				flow_type:'',
				loadStatus: 'more',
				list:[],
				randomBgColor:''
			}
		},
		onLoad() {
			this.getUsdtLog();
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
		font-size: 18px;
		// font-weight: bold;
		margin-bottom: 5rpx;
	}

	.yue {
		font-size: 14px;
	}
</style>