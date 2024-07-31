<div class="ac-load-more-remove p-1">
    <input type="hidden" id="total" name="total" value="{{ $paginator->total() }}" />
    <input type="hidden" id="page" name="page" value="{{ $paginator->currentPage() }}" />
    @if ($paginator->hasPages())
        <div class="d-flex align-items-center">
            <div class="text-muted">
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }}
                of {{ $paginator->total() }} entries
            </div>
            <div class="ml-auto">
                <ul class="pagination ac-ajax-pagination m-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&lsaquo; Prev</span></li>
                    @else
                        <li class="page-item" data-page="{{ $paginator->currentPage() - 1 }}">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                                &lsaquo; Prev
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled">
                                <a class="page-link">{{ $element }}</a>
                            </li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" data-page="{{ $page }}">
                                        <a class="page-link">{{ $page }}</a>
                                    </li>
                                @else
                                    <li class="page-item" data-page="{{ $page }}">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item" data-page="{{ $paginator->currentPage() + 1 }}">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                                next &rsaquo;
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled"><a class="page-link">next &rsaquo;</a></li>
                    @endif
                </ul>
            </div>
        </div>
    @endif
</div>
