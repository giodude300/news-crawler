import type { PaginatedExecutions, ProcessResponse, ProcessingExecution, StoryFilter } from './types';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? '/api';

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
    const response = await fetch(`${API_BASE_URL}${path}`, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...options.headers,
        },
    });

    if (!response.ok) {
        const payload = await response.json().catch(() => null);
        const message = payload?.message ?? `Request failed with status ${response.status}`;
        throw new Error(message);
    }

    return response.json() as Promise<T>;
}

export const api = {
    processStories: () => request<ProcessResponse>('/stories/process', { method: 'POST' }),
    getExecutions: (page = 1, perPage = 10) => request<PaginatedExecutions>(`/executions?page=${page}&per_page=${perPage}`),
    getExecution: (id: number, filter?: StoryFilter) => {
        const query = filter ? `?filter=${filter}` : '';
        return request<ProcessingExecution>(`/executions/${id}${query}`);
    },
};
