export const ConsoleMock: jest.Mocked< Pick< Console, 'log' | 'info' | 'warn' | 'error' | 'debug' > > = {
	log: jest.fn(),
	info: jest.fn(),
	warn: jest.fn(),
	error: jest.fn(),
	debug: jest.fn(),
};
