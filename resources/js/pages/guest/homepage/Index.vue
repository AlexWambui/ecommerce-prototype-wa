<script>
</script>

<template>
    <div class="HomePage">
		<section class="Hero mb-15">
			<div class="container-fluid grid lg:grid-cols-12 gap-8">
				<div class="content lg:col-span-7 space-y-4">
					<h1 class="title font-bold text-l-text lg:text-xl-text uppercase">Walk with confidence <br> Step in Style</h1>
					<div class="actions bg-amber-400 font-semibold py-2 px-4 inline-block rounded-sm">
						<a href="/shop">Start Shopping</a>
					</div>
				</div>

				<div class="image lg:col-span-5 h-[30dvh] lg:h-full">
					<img src='/assets/images/air-force-sneaker-hero-image.png' alt="Air Force shoe hero image" class="object-contain">
				</div>
			</div>
		</section>

		<section class="Brands">
			<div class="container-fluid">
				<div class="brands-wrapper">
					<div class="brand">
						<img src={ AppConfig.images.nikeLogo.src } alt="Nike logo">
					</div>

					<div class="brand">
						<img src={ AppConfig.images.vansLogo.src } alt="Vans logo">
					</div>

					<div class="brand">
						<img src={ AppConfig.images.newbalanceLogo.src } alt="New Balance logo">
					</div>

					<div class="brand">
						<img src={ AppConfig.images.airjordanLogo.src } alt="Air Jordan logo">
					</div>

					<div class="brand">
						<img src={ AppConfig.images.converseLogo.src } alt="Converse logo">
					</div>

					<div class="brand w-50 h-50">
						<img src={ AppConfig.images.alexanderMcqueenLogo.src } alt="Alexander Mcqueen logo">
					</div>

					<div class="brand w-50 h-50">
						<img src={ AppConfig.images.adidasLogo.src } alt="Adidas logo">
					</div>
				</div>
			</div>
		</section>

		<section class="NewArrivals">
			<div class="container-fluid">
				<div class="section-header">
					<h2>New Arrivals</h2>
				</div>

				<div class="content">
					{newArrivals.map((shoe) => (
						<div class="shoe-card">
							<div class="image">
								<img src={ shoe.image.src } alt="Shoe">
							</div>

							<div class="details">
								<p class="name">{shoe.name}</p>
								<p class="price">{new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(shoe.price)}</p>
							</div>
						</div>
					))}
				</div>
			</div>
		</section>

		<section class="MostPopular">
			<div class="container-fluid">
				<div class="section-header">
					<h2>Most Popular</h2>
				</div>

				<div class="content">
					{mostPopular.map((shoe) => (
						<div class="shoe-card">
							<div class="image">
								<img src={ shoe.image.src } alt="Shoe">
							</div>

							<div class="details">
								<p class="name">{shoe.name}</p>
								<p class="price">{new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(shoe.price)}</p>
							</div>
						</div>
					))}
				</div>
			</div>
		</section>
	</div>
</template>