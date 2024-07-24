import {
    Chart,
    BarElement,
    ArcElement,
    LineElement,
    PointElement,
    BarController,
    DoughnutController,
    LineController,
    CategoryScale,
    LinearScale,
    Tooltip
  } from 'chart.js';

import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(
    BarElement,
    ArcElement,
    LineElement,
    PointElement,
    BarController,
    DoughnutController,
    LineController,
    CategoryScale,
    LinearScale,
    Tooltip,
    ChartDataLabels
);