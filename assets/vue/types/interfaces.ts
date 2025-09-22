export interface GameInterface {
    type: string;
    id: number;
    userId: number;
    homeClub: string;
    awayClub: string;
    homeGoals: number | null;
    awayGoals: number | null;
    competition: string;
    season: string;
    matchday: number;
    date: { date: string; }
    betHomeGoals: number;
    betAwayGoals: number;
    betStatus: string | null;
    betPoints: number | null;
}
