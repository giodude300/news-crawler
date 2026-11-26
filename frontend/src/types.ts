export type StoryFilter = 'long_titles' | 'short_titles';

export interface Story {
    id?: number;
    hacker_news_id?: number;
    title: string;
    points: number;
    comments: number;
    word_count: number;
    filter_type: StoryFilter;
    position: number;
}

export interface ProcessingExecution {
    id: number;
    requested_at: string;
    filters_applied: StoryFilter[];
    stories_count: number;
    duration_ms: number;
    long_titles_count?: number;
    short_titles_count?: number;
    stories?: Story[];
}

export interface PaginatedExecutions {
    data: ProcessingExecution[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface ProcessResponse {
    execution: ProcessingExecution;
    long_titles: Story[];
    short_titles: Story[];
}

