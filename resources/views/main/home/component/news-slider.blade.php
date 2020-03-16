<div class="clearfix"></div>
<div class="container-fluid">
    <div id="news-carousel" class="carousel slide carousel-fade" data-ride="carousel">
        <div class="carousel-inner" role="listbox">
        	@forelse ($news as $index => $new)
            <div class="carousel-item {{ $index == 1 ? 'active' : ''}} text-center">
                <div class="d-flex align-items-center w-100">
                	<div class="text-center align-self-center mx-auto"> 
                		<a class="{{ $new->class ?? 'btn-link' }}" target="{{ $new->target ?? '_blank' }}" href="{{ $new->link }}">{!! $new->text !!}</a>
                	</div>
                </div>
            </div>
            @empty
            <div class="carousel-item text-center active">
                <div class="d-flex align-items-center w-100">
                	<div class="text-center align-self-center mx-auto">
	                	{{ __('Mobile App coming soon...') }} 
	                </div>
                </div>
            </div>
            @endforelse
        </div>
        <a class="carousel-control-prev" href="#news-carousel" role="button" data-slide="prev">
            <span class="fa fa-angle-double-left font-weight-bold text-buzzex" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#news-carousel" role="button" data-slide="next">
            <span class="fa fa-angle-double-right font-weight-bold text-buzzex" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>
