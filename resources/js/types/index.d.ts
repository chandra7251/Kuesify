export interface User {
    id: number;
    name: string;
    email: string;
    avatar_key?: string | null;
    email_verified_at?: string;
}

export interface OrganizationInfo {
    id: number;
    name: string;
    role: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    currentOrganization?: OrganizationInfo | null;
};
