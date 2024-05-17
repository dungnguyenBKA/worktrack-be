@if ($paginate->lastPage() > 1)
    <div class="d-flex justify-content-end mb-2">
        @if ($paginate->currentPage() > 1)
            <a class="btn btn-outline-info" href="{{ $paginate->appends(request()->query())->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
        @endif
        @if ($paginate->currentPage() >= 3)
            <a class="btn btn-outline-info mx-1" href="{{ $paginate->path() }}">1</a>
        @endif
        @if ($paginate->currentPage() > 3)
            <a class="btn btn-outline-info mx-1 font-weight-bold">...</a>
        @endif
        @if ($paginate->currentPage() > 1)
            <a class="btn btn-outline-info mx-1 font-weight-bold" href="{{ $paginate->appends(request()->query())->previousPageUrl() }}">{{ $paginate->currentPage() - 1 }}</a>
        @endif

        <a class="btn btn-info mx-1 font-weight-bold">{{ $paginate->currentPage() }}</a>

        @if($paginate->currentPage() <= $paginate->lastPage() - 1)
            <a class="btn btn-outline-info mx-1 font-weight-bold" href="{{ $paginate->appends(request()->query())->nextPageUrl() }}">{{ $paginate->currentPage() + 1 }}</a>
        @endif
        @if($paginate->lastPage() - $paginate->currentPage() >= 3)
                <a class="btn btn-outline-info mx-1 font-weight-bold">...</a>
        @endif
        @if($paginate->lastPage() >= 3 && $paginate->currentPage() < $paginate->lastPage() - 1)
            <a class="btn btn-outline-info mx-1 font-weight-bold" href="{{ $paginate->toArray()['last_page_url'] }}">{{ $paginate->lastPage() }}</a>
        @endif
        @if($paginate->currentPage() < $paginate->lastPage())
            <a class="btn btn-outline-info" href="{{ $paginate->appends(request()->query())->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
        @endif
    </div>
@endif
