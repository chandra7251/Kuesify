const labels: Record<string, string> = {
    participant: 'Siswa / Peserta',
    creator: 'Guru / Creator',
    organization_admin: 'Admin Organisasi',
    super_admin: 'Admin Platform',
};

export function roleLabel(role: string | null | undefined): string {
    return labels[role ?? ''] ?? 'Belum memiliki role';
}
