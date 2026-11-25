import { afterEach, describe, expect, it } from 'vitest';
import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import App from './App';

afterEach(() => {
    cleanup();
});

describe('App', () => {
    it('renders the application successfully', () => {
        render(<App />);

        expect(
            screen.getByRole('heading', {
                name: 'Get started',
            }),
        ).toBeTruthy();
    });

    it('increments the counter when clicked', () => {
        render(<App />);

        const counterButton = screen.getByRole('button', {
            name: 'Count is 0',
        });

        fireEvent.click(counterButton);

        expect(
            screen.getByRole('button', {
                name: 'Count is 1',
            }),
        ).toBeTruthy();
    });
});
