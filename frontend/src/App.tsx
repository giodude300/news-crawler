import { useCallback, useEffect, useMemo, useState } from 'react';
import { api } from './api';
import type { ProcessingExecution, ProcessResponse, Story, StoryFilter } from './types';
import './App.css';

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function formatNumber(value: number): string {
    return new Intl.NumberFormat('en-US').format(value);
}

function BrandMark() {
    return (
        <div className="brand" aria-label="Stack Builders">
            <img
                className="brand-logo"
                src="https://getonbrd-prod.s3.amazonaws.com/uploads/users/logo/8560/SB_Logohorizontal-01.png"
                alt="Stack Builders"
            />
        </div>
    );
}

function MetricCard({ label, value, detail, icon }: { label: string; value: string; detail: string; icon: string }) {
    return (
        <article className="metric-card">
            <div className="metric-topline">
                <span className="metric-label">{label}</span>
                <span className="metric-icon">{icon}</span>
            </div>
            <strong className="metric-value">{value}</strong>
            <span className="metric-detail">{detail}</span>
        </article>
    );
}

function StoryTable({ stories, type }: { stories: Story[]; type: StoryFilter }) {
    const isLong = type === 'long_titles';

    return (
        <section className="panel stories-panel">
            <div className="panel-heading">
                <div>
                    <div className="eyebrow">{isLong ? 'SORTED BY COMMENTS' : 'SORTED BY POINTS'}</div>
                    <h2>{isLong ? 'Long titles' : 'Short titles'}</h2>
                </div>
                <span className={`count-pill ${isLong ? 'count-orange' : 'count-blue'}`}>{stories.length} stories</span>
            </div>
            {stories.length === 0 ? (
                <div className="empty-state compact-empty">
                    <span className="empty-icon">◇</span>
                    <p>No stories in this category yet.</p>
                </div>
            ) : (
                <div className="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th className="rank-column">#</th>
                                <th>Story</th>
                                <th>Words</th>
                                <th>Points</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            {stories.map((story, index) => (
                                <tr key={`${story.hacker_news_id}-${index}`}>
                                    <td className="rank-cell">{index + 1}</td>
                                    <td>
                                        <a
                                            className="story-title"
                                            href={`https://news.ycombinator.com/item?id=${story.hacker_news_id ?? story.id ?? ''}`}
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            {story.title}
                                            <span className="external-icon">↗</span>
                                        </a>
                                        <span className="story-id">HN #{story.hacker_news_id ?? story.id}</span>
                                    </td>
                                    <td>{story.word_count}</td>
                                    <td><span className="stat-number">{formatNumber(story.points)}</span></td>
                                    <td><span className="stat-number">{formatNumber(story.comments)}</span></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
}

function SkeletonBlock({ className = '' }: { className?: string }) {
    return <div className={`skeleton ${className}`} aria-hidden="true" />;
}

function MetricsSkeleton() {
    return (
        <section className="metrics-grid" aria-label="Loading execution metrics">
            {Array.from({ length: 4 }).map((_, index) => (
                <article className="metric-card skeleton-card" key={index}>
                    <div className="metric-topline">
                        <SkeletonBlock className="skeleton-label" />
                        <SkeletonBlock className="skeleton-icon" />
                    </div>
                    <SkeletonBlock className="skeleton-value" />
                    <SkeletonBlock className="skeleton-detail" />
                </article>
            ))}
        </section>
    );
}

function StoryTablesSkeleton() {
    return (
        <div className="stories-grid" aria-label="Loading stories">
            {[0, 1].map((item) => (
                <section className="panel stories-panel skeleton-table-card" key={item}>
                    <div className="panel-heading">
                        <div>
                            <SkeletonBlock className="skeleton-label" />
                            <SkeletonBlock className="skeleton-heading" />
                        </div>
                        <SkeletonBlock className="skeleton-pill" />
                    </div>
                    <div className="skeleton-table">
                        {Array.from({ length: 5 }).map((_, index) => (
                            <div className="skeleton-table-row" key={index}>
                                <SkeletonBlock className="skeleton-cell short" />
                                <SkeletonBlock className="skeleton-cell" />
                                <SkeletonBlock className="skeleton-cell medium" />
                            </div>
                        ))}
                    </div>
                </section>
            ))}
        </div>
    );
}

function HistorySkeleton() {
    return (
        <div className="skeleton-history" aria-label="Loading execution history">
            {Array.from({ length: 5 }).map((_, index) => (
                <div className="skeleton-history-row" key={index}>
                    <SkeletonBlock className="skeleton-cell short" />
                    <SkeletonBlock className="skeleton-cell medium" />
                    <SkeletonBlock className="skeleton-cell short" />
                    <SkeletonBlock className="skeleton-cell medium" />
                    <SkeletonBlock className="skeleton-cell short" />
                </div>
            ))}
        </div>
    );
}

function App() {
    const [executions, setExecutions] = useState<ProcessingExecution[]>([]);
    const [selectedExecution, setSelectedExecution] = useState<ProcessingExecution | null>(null);
    const [result, setResult] = useState<ProcessResponse | null>(null);
    const [loading, setLoading] = useState(true);
    const [loadingExecution, setLoadingExecution] = useState(false);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [activeFilter, setActiveFilter] = useState<'all' | StoryFilter>('all');

    const loadExecutions = useCallback(async (currentPage = 1, pageSize = perPage) => {
        try {
            const response = await api.getExecutions(currentPage, pageSize);
            setExecutions(response.data);
            setPage(response.current_page);
            setLastPage(response.last_page);
        } catch (requestError) {
            setError(requestError instanceof Error ? requestError.message : 'Unable to load execution history.');
        } finally {
            setLoading(false);
        }
    }, [perPage]);

    useEffect(() => {
        let cancelled = false;

        async function fetchExecutions() {
            try {
                const response = await api.getExecutions(1, perPage);

                if (cancelled) {
                    return;
                }

                setExecutions(response.data);
                setPage(response.current_page);
                setLastPage(response.last_page);
            } catch (requestError) {
                if (!cancelled) {
                    setError(
                        requestError instanceof Error
                            ? requestError.message
                            : 'Unable to load execution history.',
                    );
                }
            } finally {
                if (!cancelled) {
                    setLoading(false);
                }
            }
        }

        void fetchExecutions();

        return () => {
            cancelled = true;
        };
    }, [perPage]);

    const displayedStories = useMemo(() => {
        if (!selectedExecution?.stories) {
            return [];
        }

        return selectedExecution.stories;
    }, [selectedExecution]);

    async function handleProcess() {
        setProcessing(true);
        setError(null);

        try {
            const response = await api.processStories();
            setResult(response);
            setSelectedExecution(null);
            setActiveFilter('all');
            await loadExecutions(1);
        } catch (requestError) {
            setError(requestError instanceof Error ? requestError.message : 'The crawler could not be executed.');
        } finally {
            setProcessing(false);
        }
    }

    async function handleSelectExecution(execution: ProcessingExecution) {
        setError(null);
        setLoadingExecution(true);
        setSelectedExecution(null);
        setResult(null);

        try {
            const detail = await api.getExecution(execution.id);
            setSelectedExecution(detail);
            setActiveFilter('all');
        } catch (requestError) {
            setError(requestError instanceof Error ? requestError.message : 'Unable to load execution details.');
        } finally {
            setLoadingExecution(false);
        }
    }

    const longStories = result
        ? result.long_titles
        : displayedStories.filter((story) => story.filter_type === 'long_titles');
    const shortStories = result
        ? result.short_titles
        : displayedStories.filter((story) => story.filter_type === 'short_titles');
    const currentExecution = result?.execution ?? selectedExecution;
    const totalStories = currentExecution?.stories_count ?? 0;
    const longCount = currentExecution?.long_titles_count ?? longStories.length;
    const shortCount = currentExecution?.short_titles_count ?? shortStories.length;

    return (
        <div className="app-shell">
            <aside className="sidebar">
                <div className="sidebar-brand"><BrandMark /></div>
                <div className="sidebar-section-label">WORKSPACE</div>
                <a className="nav-item active" href="#top">
                    <span className="nav-icon">▦</span>
                    <span>Overview</span>
                </a>
                <div className="sidebar-section-label history-label">DATA</div>
                <a className="nav-item" href="#execution-history">
                    <span className="nav-icon">◷</span>
                    <span>Execution history</span>
                </a>
                <a className="nav-item" href="/api/documentation" target="_blank" rel="noreferrer">
                    <span className="nav-icon">⌘</span>
                    <span>API Documentation</span>
                </a>
                <div className="sidebar-bottom">
                    <div className="sidebar-status"><span className="status-dot" /> API workspace</div>
                    <span className="sidebar-version">Hacker News Crawler · v1.0</span>
                </div>
            </aside>

            <main className="main-content" id="top">
                <header className="topbar">
                    <div className="breadcrumb">Workspace <span>/</span> <strong>Overview</strong></div>
                    <div className="topbar-right"><span className="live-indicator" /> API connected</div>
                </header>

                <div className="content-container">
                    <section className="page-heading">
                        <div>
                            <div className="eyebrow">HACKER NEWS · DATA PROCESSING</div>
                            <h1>News Crawler - technical assessment</h1>
                            <p>Extract, classify, and explore the latest stories from Hacker News.</p>
                        </div>
                        <button className="primary-button" type="button" onClick={handleProcess} disabled={processing}>
                            {processing ? <span className="spinner" aria-hidden="true" /> : <span className="button-plus" aria-hidden="true">↻</span>}
                            {processing ? 'Processing stories...' : 'Run crawler'}
                        </button>
                    </section>

                    {error && (
                        <div className="alert-error" role="alert">
                            <span>!</span>
                            <div><strong>Something went wrong</strong><p>{error}</p></div>
                            <button type="button" onClick={() => setError(null)} aria-label="Dismiss error">×</button>
                        </div>
                    )}

                    {loading || loadingExecution ? (
                        <MetricsSkeleton />
                    ) : (
                        <section className="metrics-grid" aria-label="Execution metrics">
                            <MetricCard label="Stories processed" value={formatNumber(totalStories)} detail="Latest execution" icon="▤" />
                            <MetricCard label="Long titles" value={formatNumber(longCount)} detail="More than 5 words · By comments" icon="↗" />
                            <MetricCard label="Short titles" value={formatNumber(shortCount)} detail="5 words or fewer · By points" icon="↘" />
                            <MetricCard label="Execution time" value={currentExecution ? `${currentExecution.duration_ms} ms` : '—'} detail="Latest processing run" icon="◷" />
                        </section>
                    )}

                    <section className="results-section">
                        <div className="section-heading">
                            <div>
                                <div className="eyebrow">LATEST RESULTS</div>
                                <h2>{currentExecution ? `Execution #${currentExecution.id}` : 'Stories overview'}</h2>
                            </div>
                            {currentExecution && <span className="execution-date">{formatDate(currentExecution.requested_at)}</span>}
                        </div>

                        {loading || loadingExecution ? (
                            <StoryTablesSkeleton />
                        ) : !currentExecution ? (
                            <div className="panel empty-state main-empty">
                                <div className="empty-illustration">⌁</div>
                                <h3>Your next insight starts here</h3>
                                <p>Run the crawler to fetch the 30 latest Hacker News stories and see how they rank.</p>
                                <button className="secondary-button" type="button" onClick={handleProcess} disabled={processing}>
                                    {processing ? 'Processing...' : 'Run your first crawl'}
                                </button>
                            </div>
                        ) : (
                            <>
                                <div className="filter-tabs" role="group" aria-label="Story category filter">
                                    <button className={activeFilter === 'all' ? 'selected' : ''} onClick={() => setActiveFilter('all')} type="button">All stories</button>
                                    <button className={activeFilter === 'long_titles' ? 'selected' : ''} onClick={() => setActiveFilter('long_titles')} type="button">Long titles</button>
                                    <button className={activeFilter === 'short_titles' ? 'selected' : ''} onClick={() => setActiveFilter('short_titles')} type="button">Short titles</button>
                                </div>
                                <div className="stories-grid">
                                    {(activeFilter === 'all' || activeFilter === 'long_titles') && <StoryTable stories={longStories} type="long_titles" />}
                                    {(activeFilter === 'all' || activeFilter === 'short_titles') && <StoryTable stories={shortStories} type="short_titles" />}
                                </div>
                            </>
                        )}
                    </section>

                    <section className="panel history-panel" id="execution-history">
                        <div className="panel-heading history-heading">
                            <div>
                                <div className="eyebrow">YOUR ACTIVITY</div>
                                <h2>Execution history</h2>
                                <p className="panel-description">Review previous crawler runs and their results.</p>
                            </div>
                            <span className="count-pill">{formatNumber(executions.length)} shown</span>
                        </div>
                        {loading ? (
                            <HistorySkeleton />
                        ) : executions.length === 0 ? (
                            <div className="history-loading">No executions yet. Run the crawler to get started.</div>
                        ) : (
                            <div className="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Execution</th><th>Date requested</th><th>Stories</th><th>Duration</th><th>Long titles</th><th>Short titles</th><th /></tr>
                                    </thead>
                                    <tbody>
                                        {executions.map((execution) => (
                                            <tr key={execution.id}>
                                                <td><strong className="execution-id">#{execution.id}</strong></td>
                                                <td>{formatDate(execution.requested_at)}</td>
                                                <td>{execution.stories_count}</td>
                                                <td>{execution.duration_ms} ms</td>
                                                <td>{execution.long_titles_count ?? '—'}</td>
                                                <td>{execution.short_titles_count ?? '—'}</td>
                                                <td className="action-cell"><button type="button" onClick={() => void handleSelectExecution(execution)}>View details <span>→</span></button></td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                        {executions.length > 0 && (
                            <div className="pagination">
                                <div className="pagination-size">
                                    <label htmlFor="history-page-size">Rows per page</label>
                                    <select
                                        id="history-page-size"
                                        value={perPage}
                                        onChange={(event) => {
                                            const nextPerPage = Number(event.target.value);
                                            setPerPage(nextPerPage);
                                        }}
                                    >
                                        <option value={5}>5</option>
                                        <option value={10}>10</option>
                                        <option value={20}>20</option>
                                        <option value={30}>30</option>
                                    </select>
                                </div>
                                <span>Page {page} of {lastPage} · {formatNumber(executions.length)} on this page</span>
                                <div>
                                    <button type="button" disabled={loading || page <= 1} onClick={() => void loadExecutions(page - 1)}>Previous</button>
                                    <button type="button" disabled={loading || page >= lastPage} onClick={() => void loadExecutions(page + 1)}>Next</button>
                                </div>
                            </div>
                        )}
                    </section>

                    <footer className="footer"><span>Built for the Stack Builders coding assessment.</span><span>Powered by Hacker News API · Developed by Giovanny Yanez</span></footer>
                </div>
            </main>
        </div>
    );
}

export default App;
