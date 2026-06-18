<template>
	<view>
		<view class="list">
			<view class="item u-border-bottom" v-for="(vo,index) in list" :key="index">
				<view class="icon">
					<u-avatar text="充" randomBgColor></u-avatar>														
				</view>
				<view class="info" @click="copyOrder(vo.bianhao)">
					<view class="time u-info">{{vo.bianhao}}</view>
					<view class="">{{vo.createtime }}</view>
				</view>
				<view class="money">
					<view class="change u-primary">{{vo.num}}</view>
					<view class="yue">
						<template v-if="vo.status=='normal'">
							已审核
						</template>		
						<template v-else>
							待审核
						</template>											
					</view>
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
			copyOrder(text){
				uni.setClipboardData({
					data:text,
					success:()=>{
						uni.$u.toast('复制成功');
					}
				})
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
				uni.$u.http.post("/api/recharge/index",{
					page:this.page
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