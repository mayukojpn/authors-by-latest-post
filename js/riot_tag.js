/**
* Custom Tag: Cards
* Generate author profile cards.
*/
<cards>
<div each={ users } class="author">

	<div class='header'>

		<a class="avatar" href={ link }>
			<img class="icon" src={ avatar_urls['96'] } />
		</a>
		<a class="header-image"  href={ link }>
			<img src={ posts[0].thumbnail }>
		</a>

	</div>

	<div class='body'>

	<a class="name" href={ link }>{ name }</a> <time>最後の更新: { posts[0].published }前</time>


		<div class='row'>
			<posts posts={ posts }/>
			<a class="more" if={ post_count > 3 } href={ link }>もっと見る →</a>
		</div><!-- end .row -->

	</div>
	</div>

<style scoped type='less'>
@media (min-width: 767px) {
	:scope {
		display: grid;
		grid-template-columns: 1fr 1fr;
		grid-gap: 20px;
		margin-bottom: 20px;
	}
}
	:scope {

		.author {
			border: 1px solid #e9e9e9;
			min-height: 200px;
			margin-bottom: 20px;
		}

		.row {
			margin-top: 12px;
			font-size: 12px;
		}

		.header {
			position: relative;
			min-height: 120px;
			img {
				width: 100%;
			}
			.avatar {
				position: absolute;
				bottom: -60px;
				left: 16px;
				z-index: 1;
				img {
					width: 96px;
					border-radius: 50%;
					border: 4px solid white;
				}
			}
			.header-image {
		    display: block;
				position: relative;
				height: 120px;
				width: 100%;
				overflow: hidden;
				background-color: #eee;
				img {
					position: relative;
					top: 50%;
					transform: translateY(-50%);
				}
			}
		}
		.body {
			background-color: white;
			padding: 16px;
			.name {
				margin-left: 106px;
				font-size: 21px;
				font-weight: bold;
			}
			.name + time {
				margin-left: 106px;
				display: inline-block;
			}
			.row {
				text-align: right;
			}
			posts {
				text-align: left;
			}
		}
	}
</style>
	var self     = this;
	jQuery(function($) {
		$.ajax({
			url: resource_url + '/wp-json/wp/v2/users',
			type:'GET',
			dataType: 'json',
			data : {
				page: opts.count,
				per_page: opts.per_page,
				filter : {
				}
			},
			timeout:10000,
		}).done(function( users ){
			self.users = users
			self.update()
		});
		$(window).on("scroll", function() {
			var scrollHeight   = $('#authors-by-latest-post').height() + $('#authors-by-latest-post').offset().top;
			var scrollPosition = $(window).height() + $(window).scrollTop();
			if (
				scrollHeight - scrollPosition <= 0
				&& $('#authors-by-latest-post').children().last().children().length == opts.per_page
				&& $('#authors-by-latest-post').children().last().attr('count')     == opts.count
			) {
				opts.count++;
				$('#authors-by-latest-post').append('<div id="author-list-' + opts.count + '" count="' + opts.count + '"></div>');
				riot.mount( 'div#author-list-' + opts.count, 'cards' );
			}
		});
	});


</cards>

/**
* Custom Tag: Raw
* Render unescaped HTML
* http://riotjs.com/ja/guide/#html
*/
<raw>
<span></span>
this.root.innerHTML = opts.content
</raw>

/**
* Custom Tag: Format Date
*/
<format-date>
<time>{ formatted }<time>

<style scoped type='less'>
:scope {
	display: inline-block;
}
</style>
var date = new Date(opts.date),
	y = date.getFullYear(),
	m = date.getMonth() + 1,
	d = date.getDate(),
	days = ["日", "月", "火", "水", "木", "金", "土"],
	day  = days[date.getDay()];
this.formatted = y +'/'+ m +'/'+ d +' ('+ day +')';
</format-date>

/**
* Custom Tag: Posts
* list specific author's latest posts.
*/
<posts>
<virtual each={ opts.posts }>
	<format-date date={ time }></format-date>
	<a href={ permalink }><raw content={ title } /></a>
</virtual>
<style scoped type='less'>
	:scope {
		display: grid;
		grid-template-columns: 8em auto;
	}
</style>
</posts>
