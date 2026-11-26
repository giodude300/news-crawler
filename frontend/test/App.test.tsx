import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react';
import App from '../src/App';
import { api } from '../src/api';
import type { PaginatedExecutions, ProcessResponse, ProcessingExecution, Story } from '../src/types';

vi.mock('../src/api', () => ({
    api: {
        processStories: vi.fn(),
        getExecutions: vi.fn(),
        getExecution: vi.fn(),
    },
}));

const longStory: Story = {
    id: 101,
    hacker_news_id: 101,
    title: 'A detailed story title containing more than five words',
    points: 120,
    comments: 45,
    word_count: 9,
    filter_type: 'long_titles',
    position: 1,
};

const shortStory: Story = {
    id: 202,
    hacker_news_id: 202,
    title: 'Short story title',
    points: 250,
    comments: 12,
    word_count: 3,
    filter_type: 'short_titles',
    position: 1,
};

const execution: ProcessingExecution = {
    id: 1,
    requested_at: '2026-11-26T10:00:00Z',
    filters_applied: ['long_titles', 'short_titles'],
    stories_count: 2,
    duration_ms: 350,
    long_titles_count: 1,
    short_titles_count: 1,
    stories: [longStory, shortStory],
};

const paginatedExecutions: PaginatedExecutions = {
    data: [execution],
    current_page: 1,
    last_page: 2,
    per_page: 10,
    total: 11,
};

const processResponse: ProcessResponse = {
    execution,
    long_titles: [longStory],
    short_titles: [shortStory],
};

function mockInitialHistory(response = paginatedExecutions) {
    vi.mocked(api.getExecutions).mockResolvedValue(response);
}

beforeEach(() => {
    vi.clearAllMocks();
    mockInitialHistory();
});

afterEach(() => {
    cleanup();
});

describe('News Crawler dashboard', () => {
    it('renders the dashboard and its main actions', async () => {
        render(<App />);

        expect(
            screen.getByRole('heading', {
                name: 'News Crawler - technical assessment',
            }),
        ).toBeTruthy();

        expect(
            screen.getByRole('button', {
                name: 'Run crawler',
            }),
        ).toBeTruthy();

        expect(
            await screen.findByRole('heading', {
                name: 'Execution history',
            }),
        ).toBeTruthy();

        expect(screen.getByText('#1')).toBeTruthy();
    });

    it('shows the empty state when there are no previous executions', async () => {
        mockInitialHistory({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        });

        render(<App />);

        expect(
            await screen.findByText('Your next insight starts here'),
        ).toBeTruthy();

        expect(
            screen.getByText('No executions yet. Run the crawler to get started.'),
        ).toBeTruthy();
    });

    it('processes stories and displays the returned results', async () => {
        vi.mocked(api.processStories).mockResolvedValue(processResponse);

        render(<App />);

        fireEvent.click(
            await screen.findByRole('button', {
                name: 'Run crawler',
            }),
        );

        expect(await screen.findByText('Execution #1')).toBeTruthy();
        expect(screen.getByText(longStory.title)).toBeTruthy();
        expect(screen.getByText(shortStory.title)).toBeTruthy();
        expect(api.processStories).toHaveBeenCalledOnce();

        await waitFor(() => {
            expect(api.getExecutions).toHaveBeenLastCalledWith(1, 10);
        });
    });

    it('shows an error message when story processing fails', async () => {
        vi.mocked(api.processStories).mockRejectedValue(
            new Error('Crawler service unavailable'),
        );

        render(<App />);

        fireEvent.click(
            await screen.findByRole('button', {
                name: 'Run crawler',
            }),
        );

        expect(await screen.findByRole('alert')).toBeTruthy();
        expect(screen.getByText('Crawler service unavailable')).toBeTruthy();
    });

    it('filters the results by long and short titles', async () => {
        vi.mocked(api.processStories).mockResolvedValue(processResponse);

        render(<App />);

        fireEvent.click(
            await screen.findByRole('button', {
                name: 'Run crawler',
            }),
        );

        await screen.findByText(longStory.title);

        fireEvent.click(
            screen.getByRole('button', {
                name: 'Long titles',
            }),
        );

        expect(screen.getByText(longStory.title)).toBeTruthy();
        expect(screen.queryByText(shortStory.title)).toBeNull();

        fireEvent.click(
            screen.getByRole('button', {
                name: 'Short titles',
            }),
        );

        expect(screen.getByText(shortStory.title)).toBeTruthy();
        expect(screen.queryByText(longStory.title)).toBeNull();
    });

    it('loads and displays the details of a previous execution', async () => {
        vi.mocked(api.getExecution).mockResolvedValue(execution);

        render(<App />);

        fireEvent.click(
            await screen.findByRole('button', {
                name: /View details/,
            }),
        );

        expect(await screen.findByText('Execution #1')).toBeTruthy();
        expect(screen.getByText(longStory.title)).toBeTruthy();
        expect(screen.getByText(shortStory.title)).toBeTruthy();
        expect(api.getExecution).toHaveBeenCalledWith(1);
    });


    it('loads the next page of execution history', async () => {
        vi.mocked(api.getExecutions)
            .mockResolvedValueOnce(paginatedExecutions)
            .mockResolvedValueOnce({
                ...paginatedExecutions,
                current_page: 2,
            });

        render(<App />);

        fireEvent.click(
            await screen.findByRole('button', {
                name: 'Next',
            }),
        );

        await waitFor(() => {
            expect(api.getExecutions).toHaveBeenLastCalledWith(2, 10);
        });
    });


    it('reloads the execution history when the page size changes', async () => {
        render(<App />);

        const pageSize = await screen.findByLabelText('Rows per page');

        fireEvent.change(pageSize, {
            target: {
                value: '5',
            },
        });

        await waitFor(() => {
            expect(api.getExecutions).toHaveBeenLastCalledWith(1, 5);
        });
    });
});
